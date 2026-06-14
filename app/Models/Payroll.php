<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'month', 'year', 'salary_base', 'overtime_pay',
        'bonuses', 'cnss_deduction', 'irpp_deduction', 'other_deductions',
        'gross_salary', 'net_salary', 'status', 'fiche_de_paie_path',
    ];

    protected $casts = [
        'salary_base' => 'decimal:3',
        'overtime_pay' => 'decimal:3',
        'bonuses' => 'decimal:3',
        'cnss_deduction' => 'decimal:3',
        'irpp_deduction' => 'decimal:3',
        'other_deductions' => 'decimal:3',
        'gross_salary' => 'decimal:3',
        'net_salary' => 'decimal:3',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function calculateCNSS(): float
    {
        return round(($this->gross_salary ?? 0) * 0.0918, 3);
    }

    public function calculateIRPP(): float
    {
        $gross = $this->gross_salary ?? 0;
        if ($gross <= 2000) return 0;
        if ($gross <= 5000) return round(($gross - 2000) * 0.20, 3);
        if ($gross <= 10000) return round(600 + (($gross - 5000) * 0.30), 3);
        return round(1500 + (($gross - 10000) * 0.40), 3);
    }

    public function scopeByMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }
}
