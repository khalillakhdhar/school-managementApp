<?php
namespace App\Filament\Staff\Pages;

use App\Models\Payroll;
use Filament\Pages\Page;

class MyPayslips extends Page
{
    protected string $view = 'filament.staff.pages.my-payslips';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string { return 'Mes fiches de paie'; }
    public static function getNavigationGroup(): ?string { return 'Mon espace'; }
    public function getTitle(): string { return 'Mes fiches de paie'; }

    protected function getViewData(): array
    {
        $emp = auth()->user()?->employee;
        if (! $emp) {
            return ['payslips' => collect(), 'totalNet' => 0, 'year' => now()->year];
        }

        $months = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $payslips = Payroll::where('employee_id', $emp->id)
            ->orderByDesc('year')->orderByDesc('month')->get()
            ->map(fn ($p) => [
                'period' => ($months[$p->month] ?? $p->month) . ' ' . $p->year,
                'gross'  => (float) $p->gross_salary,
                'cnss'   => (float) $p->cnss_deduction,
                'irpp'   => (float) $p->irpp_deduction,
                'net'    => (float) $p->net_salary,
                'status' => $p->status,
            ]);

        $totalNet = (float) Payroll::where('employee_id', $emp->id)
            ->whereYear('period_from', now()->year)->sum('net_salary');

        return ['payslips' => $payslips, 'totalNet' => $totalNet, 'year' => now()->year];
    }
}
