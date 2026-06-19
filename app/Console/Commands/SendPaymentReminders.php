<?php
namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminders extends Command
{
    protected $signature   = 'payments:send-reminders';
    protected $description = 'Send email reminders for overdue or upcoming pending payments';

    public function handle(): int
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

        $this->info("Sent {$sent} payment reminder(s).");

        // Notification in-app aux admins : synthèse des impayés en retard.
        $overdueCount = Payment::where('status', 'pending')
            ->whereNotNull('due_date')->whereDate('due_date', '<', now())->count();
        if ($overdueCount > 0) {
            $overdueTotal = (float) Payment::where('status', 'pending')
                ->whereNotNull('due_date')->whereDate('due_date', '<', now())->sum('amount');
            $notification = \Filament\Notifications\Notification::make()
                ->title(__(':count paiement(s) en retard', ['count' => $overdueCount]))
                ->body(__('Total dû : :amount TND', ['amount' => number_format($overdueTotal, 3)]))
                ->icon('heroicon-o-exclamation-circle')
                ->color('danger')
                ->toDatabase();
            foreach (\App\Models\User::where('role', 'admin')->get() as $admin) {
                $admin->notifyNow($notification);
            }
        }

        return Command::SUCCESS;
    }
}
