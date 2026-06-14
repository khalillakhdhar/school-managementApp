<?php
namespace App\Services;

use App\Models\Student;
use App\Models\Payment;
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
            if (!empty($serviceIds)) {
                $payment->services()->attach($serviceIds);
            }
            return $payment;
        });
    }

    public function getStudentBalance(Student $student): array
    {
        $totalDue = $student->services()->sum('services.amount');
        $totalPaid = $student->payments()->where('status', 'paid')->sum('amount');
        $outstanding = max(0, $totalDue - $totalPaid);
        $daysOverdue = $this->calculateDaysOverdue($student);

        return [
            'total_due' => $totalDue,
            'total_paid' => $totalPaid,
            'outstanding' => $outstanding,
            'days_overdue' => $daysOverdue,
            'is_overdue' => $daysOverdue > 7,
            'is_suspended' => $daysOverdue > 45,
        ];
    }

    public function calculateDaysOverdue(Student $student): int
    {
        $lastPayment = $student->payments()->where('status', 'paid')->latest('payment_date')->first();
        if (!$lastPayment) return 0;
        return now()->diffInDays($lastPayment->payment_date);
    }
}
