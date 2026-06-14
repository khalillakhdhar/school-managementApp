<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index()
    {
        return Payment::with('student')->latest()->paginate(15);
    }

    public function show(Payment $payment)
    {
        return $payment->load('student', 'services');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->noContent();
    }

    public function recordPayment()
    {
        $validated = request()->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,app',
            'service_ids' => 'array',
            'service_ids.*' => 'exists:services,id',
            'reference_number' => 'nullable|string',
        ]);
        return $this->paymentService->recordPayment(
            $validated['student_id'],
            $validated['amount'],
            $validated['payment_method'],
            $validated['service_ids'] ?? [],
            $validated['reference_number'] ?? null
        );
    }
}
