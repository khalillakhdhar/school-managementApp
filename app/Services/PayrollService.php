<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function calculateMonthlyPayroll($month, $year)
    {
        return DB::transaction(function () use ($month, $year) {
            $employees = Employee::where('is_active', true)->get();
            foreach ($employees as $employee) {
                $payroll = Payroll::firstOrCreate(
                    ['employee_id' => $employee->id, 'month' => $month, 'year' => $year],
                    [
                        'salary_base' => $employee->salary_base,
                        'overtime_pay' => 0,
                        'bonuses' => 0,
                        'cnss_deduction' => 0,
                        'irpp_deduction' => 0,
                        'other_deductions' => 0,
                        'gross_salary' => $employee->salary_base,
                        'net_salary' => $employee->salary_base,
                        'status' => 'draft',
                    ]
                );
                $this->calculateDeductions($payroll);
            }
        });
    }

    private function calculateDeductions(Payroll $payroll): void
    {
        $gross = $payroll->salary_base + $payroll->overtime_pay + $payroll->bonuses;
        $payroll->gross_salary = $gross;
        $payroll->cnss_deduction = $payroll->calculateCNSS();
        $payroll->irpp_deduction = $payroll->calculateIRPP();
        $payroll->net_salary = $gross - $payroll->cnss_deduction - $payroll->irpp_deduction - $payroll->other_deductions;
        $payroll->save();
    }

    public function finalizePayroll(Payroll $payroll): void
    {
        $payroll->update(['status' => 'finalized']);
    }
}
