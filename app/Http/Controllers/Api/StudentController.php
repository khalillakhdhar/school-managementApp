<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\PaymentService;

class StudentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index()
    {
        return Student::activeOnly()->paginate(15);
    }

    public function show(Student $student)
    {
        return $student->load(['parents', 'services', 'payments']);
    }

    public function store()
    {
        $validated = request()->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'class' => 'required|string',
            'level' => 'required|string',
        ]);
        return Student::create($validated);
    }

    public function update(Student $student)
    {
        $validated = request()->validate([
            'first_name' => 'string',
            'last_name' => 'string',
            'date_of_birth' => 'date',
            'class' => 'string',
            'level' => 'string',
            'status' => 'in:active,inactive',
        ]);
        $student->update($validated);
        return $student;
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->noContent();
    }

    public function getBalance(Student $student)
    {
        return $this->paymentService->getStudentBalance($student);
    }

    public function getPayments(Student $student)
    {
        return $student->payments()->latest('payment_date')->paginate(10);
    }
}
