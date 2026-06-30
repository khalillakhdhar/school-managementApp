<?php
namespace App\Services;

use App\Models\Payment;
use App\Models\Service;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function recordPayment($studentId, $amount, $paymentMethod, $serviceIds = [], $referenceNumber = null)
    {
        return DB::transaction(function () use ($studentId, $amount, $paymentMethod, $serviceIds, $referenceNumber) {
            $payment = Payment::create([
                'student_id' => $studentId,
                'amount' => $amount,
                'payment_date' => now(),
                'payment_method' => $paymentMethod,
                'reference_number' => $referenceNumber,
                'status' => 'paid',
            ]);
            if (! empty($serviceIds)) {
                $services = Service::whereIn('id', $serviceIds)->get();
                $pivotData = $services->mapWithKeys(fn (Service $service) => [
                    $service->id => ['amount' => $service->amount],
                ])->all();

                $payment->services()->attach($pivotData);
            }

            return $payment;
        });
    }

    public function getStudentBalance(Student $student): array
    {
        $pendingAmount = (float) $student->payments()->where('status', 'pending')->sum('amount');
        $paidAmount = (float) $student->payments()->where('status', 'paid')->sum('amount');
        $overdueQuery = $student->payments()
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now());
        $overdueAmount = (float) (clone $overdueQuery)->sum('amount');
        $overdueCount = (int) (clone $overdueQuery)->count();
        $daysOverdue = $this->calculateDaysOverdue($student);

        return [
            'total_due' => $pendingAmount,
            'total_paid' => $paidAmount,
            'outstanding' => max(0, $pendingAmount),
            'pending_amount' => $pendingAmount,
            'paid_amount' => $paidAmount,
            'overdue_amount' => $overdueAmount,
            'overdue_count' => $overdueCount,
            'days_overdue' => $daysOverdue,
            'is_overdue' => $overdueCount > 0,
            'is_suspended' => $daysOverdue > 45,
        ];
    }

    public function calculateDaysOverdue(Student $student): int
    {
        $oldestOverdue = $student->payments()
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->orderBy('due_date')
            ->first();

        return $oldestOverdue ? (int) $oldestOverdue->due_date->diffInDays(now()) : 0;
    }

    // ── Transitions d'état (source unique, séparation des tâches) ──────────────

    /** Arrondi monétaire standard (millime tunisien = 3 décimales). */
    public static function money(float|int|string $amount): float
    {
        return round((float) $amount, 3);
    }

    /** Marquer un paiement comme encaissé (rôle secrétaire). */
    public function markPaid(Payment $payment): Payment
    {
        $payment->update([
            'status'       => 'paid',
            'payment_date' => $payment->payment_date ?: now()->toDateString(),
        ]);

        return $payment;
    }

    /** Valider un paiement encaissé (rôle comptable). */
    public function verify(Payment $payment, ?int $userId = null): Payment
    {
        $payment->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $userId ?? auth()->id(),
        ]);

        return $payment;
    }

    /** Annuler la validation comptable. */
    public function unverify(Payment $payment): Payment
    {
        $payment->update([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
        ]);

        return $payment;
    }
}
