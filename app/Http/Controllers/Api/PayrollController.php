<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Support\Facades\Gate;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function index()
    {
        Gate::authorize('viewAny', Payroll::class);

        $query = Payroll::with('employee')->latest();
        $user = request()->user();

        if (! $user->isAdmin()) {
            $query->where('employee_id', $user->employee?->id);
        }

        return $query->paginate(15);
    }

    public function show(Payroll $payroll)
    {
        Gate::authorize('view', $payroll);

        return $payroll->load('employee');
    }

    public function calculateMonthly()
    {
        Gate::authorize('create', Payroll::class);

        $validated = request()->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);
        $this->payrollService->calculateMonthlyPayroll($validated['month'], $validated['year']);
        return ['message' => 'Payroll calculated successfully'];
    }

    public function finalizePayroll(Payroll $payroll)
    {
        Gate::authorize('update', $payroll);

        $this->payrollService->finalizePayroll($payroll);
        return ['message' => 'Payroll finalized'];
    }
}
