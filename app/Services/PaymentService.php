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
        $totalDue = $student->payments()->where('status', 'pending')->sum('amount');
        $totalPaid = $student->payments()->where('status', 'paid')->sum('amount');
        $outstanding = max(0, $totalDue);
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
        $oldestOverdue = $student->payments()
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->orderBy('due_date')
            ->first();

        return $oldestOverdue ? (int) $oldestOverdue->due_date->diffInDays(now()) : 0;
    }
}
