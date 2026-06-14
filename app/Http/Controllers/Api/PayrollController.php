<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Services\PayrollService;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function index()
    {
        return Payroll::with('employee')->latest()->paginate(15);
    }

    public function show(Payroll $payroll)
    {
        return $payroll->load('employee');
    }

    public function calculateMonthly()
    {
        $validated = request()->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);
        $this->payrollService->calculateMonthlyPayroll($validated['month'], $validated['year']);
        return ['message' => 'Payroll calculated successfully'];
    }

    public function finalizePayroll(Payroll $payroll)
    {
        $this->payrollService->finalizePayroll($payroll);
        return ['message' => 'Payroll finalized'];
    }
}
