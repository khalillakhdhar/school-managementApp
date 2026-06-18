<?php
namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use Auditable;

    public function auditLabel(): string
    {
        return 'Fiche de paie #' . $this->id . ' — ' . number_format((float) $this->net_salary, 3) . ' TND net';
    }

    protected $fillable = [
        'employee_id', 'month', 'year',
        'period_from', 'period_to',
        'total_hours_worked', 'hourly_rate_used',
        'salary_base', 'overtime_pay', 'bonuses',
        'indemnite_transport', 'indemnite_logement', 'autres_indemnites',
        'gross_salary',
        'cnss_deduction', 'irpp_deduction', 'other_deductions', 'retenue_source',
        'cnss_patronale', 'foprolos', 'total_charge_patronale',
        'net_salary',
        'status', 'fiche_de_paie_path', 'notes',
    ];

    protected $casts = [
        'period_from'            => 'date',
        'period_to'              => 'date',
        'total_hours_worked'     => 'decimal:2',
        'hourly_rate_used'       => 'decimal:3',
        'salary_base'            => 'decimal:3',
        'overtime_pay'           => 'decimal:3',
        'bonuses'                => 'decimal:3',
        'indemnite_transport'    => 'decimal:3',
        'indemnite_logement'     => 'decimal:3',
        'autres_indemnites'      => 'decimal:3',
        'gross_salary'           => 'decimal:3',
        'cnss_deduction'         => 'decimal:3',
        'irpp_deduction'         => 'decimal:3',
        'other_deductions'       => 'decimal:3',
        'retenue_source'         => 'decimal:3',
        'cnss_patronale'         => 'decimal:3',
        'foprolos'               => 'decimal:3',
        'total_charge_patronale' => 'decimal:3',
        'net_salary'             => 'decimal:3',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isContractorPayroll(): bool
    {
        return $this->employee?->isContractor() ?? false;
    }

    // ─── Fixed salary calculations (CDI / CDD) ────────────────────────────────

    /**
     * Base soumise à CNSS : salaire de base + heures sup + primes
     * (les indemnités de transport/logement sont exonérées de CNSS en Tunisie)
     */
    public function getCotisableBaseAttribute(): float
    {
        return (float)$this->salary_base
             + (float)$this->overtime_pay
             + (float)$this->bonuses;
    }

    /** CNSS salariale : 9,18% */
    public function calculateCNSS(): float
    {
        return round($this->cotisable_base * 0.0918, 3);
    }

    /**
     * IRPP mensuel — barème Tunisie 2024.
     * Annualisé depuis la base imposable (après CNSS + abattement frais pro).
     */
    public function calculateIRPP(): float
    {
        $cnssBase   = $this->cotisable_base;
        $cnss       = $this->calculateCNSS();
        // Abattement frais professionnels : 10%, plafonné 2 000 TND/an → 166,667/mois
        $abattement = min($cnssBase * 0.10, 166.667);
        $baseAnnuelle = max(0, $cnssBase - $cnss - $abattement) * 12;

        $irppAnnuel = static::irppBarem($baseAnnuelle);

        $employee = $this->employee;
        if ($employee) {
            if (in_array($employee->situation_familiale, ['marie', 'divorce', 'veuf'])) {
                $irppAnnuel -= 300; // 300 TND/an chef de famille
            }
            $irppAnnuel -= $employee->nb_enfants * 100; // 100 TND/an par enfant
        }

        return round(max(0, $irppAnnuel) / 12, 3);
    }

    /** CNSS patronale : 16,57% */
    public function calculateCNSSPatronale(): float
    {
        return round($this->cotisable_base * 0.1657, 3);
    }

    /** FOPROLOS : 1% */
    public function calculateFOPROLOS(): float
    {
        return round($this->cotisable_base * 0.01, 3);
    }

    // ─── Contractor calculation ────────────────────────────────────────────────

    /** Brut prestataire = taux horaire × heures travaillées */
    public function calculateContractorGross(): float
    {
        return round((float)$this->hourly_rate_used * (float)$this->total_hours_worked, 3);
    }

    /**
     * Barème IRPP tunisien 2024 — 5 tranches sur revenu annuel imposable (TND).
     *   ≤ 5 000     : 0%
     *   ≤ 20 000    : 26%
     *   ≤ 30 000    : 28%
     *   ≤ 50 000    : 32%
     *   > 50 000    : 35%
     */
    public static function irppBarem(float $annualBase): float
    {
        if ($annualBase <= 5_000)  return 0;
        if ($annualBase <= 20_000) return ($annualBase - 5_000)  * 0.26;
        if ($annualBase <= 30_000) return 3_900  + ($annualBase - 20_000) * 0.28;
        if ($annualBase <= 50_000) return 6_700  + ($annualBase - 30_000) * 0.32;
        return                            13_100 + ($annualBase - 50_000) * 0.35;
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
