<?php
namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\Payment;
use App\Models\School;
use App\Support\Tenancy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminders extends Command
{
    protected $signature   = 'payments:send-reminders';
    protected $description = 'Send email reminders for overdue or upcoming pending payments (per school)';

    public function handle(): int
    {
        $totalSent = 0;

        // Each school is processed inside its own tenant context, so every
        // Eloquent query below is automatically scoped to that school.
        Tenancy::eachSchool(function (School $school) use (&$totalSent) {
            $totalSent += $this->remindForSchool($school);
        });

        $this->info("Sent {$totalSent} payment reminder(s) across all schools.");

        return Command::SUCCESS;
    }

    /** Send reminders for the current tenant (school) and notify its admins. */
    private function remindForSchool(School $school): int
    {
        $pending = Payment::with(['student.parents'])
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays(7))  // due in 7 days or already overdue
            ->get();

        $sent = 0;

        foreach ($pending as $payment) {
            $daysOverdue = now()->diffInDays($payment->due_date, false) < 0
                ? (int) now()->diffInDays($payment->due_date)
                : 0;

            // Only send if: due today, 7 days before due, or every 7 days when overdue
            $lastSent = $payment->reminder_sent_at;
            $shouldSend = !$lastSent || $lastSent->diffInDays(now()) >= 7;

            if (!$shouldSend) {
                continue;
            }

            foreach ($payment->student?->parents ?? [] as $parent) {
                if (!$parent->email) {
                    continue;
                }

                try {
                    Mail::to($parent->email)->send(new PaymentReminderMail($payment, $parent, abs($daysOverdue)));
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder to {$parent->email}: {$e->getMessage()}");
                }
            }

            $payment->update([
                'reminder_sent_at' => now(),
                'reminders_count'  => $payment->reminders_count + 1,
            ]);
        }

        // In-app digest to THIS school's admins only (scoped via the pivot).
        $overdue = Payment::where('status', 'pending')
            ->whereNotNull('due_date')->whereDate('due_date', '<', now());
        $overdueCount = $overdue->count();

        if ($overdueCount > 0) {
            $overdueTotal = (float) (clone $overdue)->sum('amount');
            $notification = \Filament\Notifications\Notification::make()
                ->title(__(':count paiement(s) en retard', ['count' => $overdueCount]))
                ->body(__('Total dû : :amount TND', ['amount' => number_format($overdueTotal, 3)]))
                ->icon('heroicon-o-exclamation-circle')
                ->color('danger')
                ->toDatabase();

            foreach ($school->users()->where('role', 'admin')->get() as $admin) {
                $admin->notifyNow($notification);
            }
        }

        return $sent;
    }
}
