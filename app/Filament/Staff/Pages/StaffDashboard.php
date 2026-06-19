<?php
namespace App\Filament\Staff\Pages;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\TimetableEntry;
use Filament\Pages\Page;

class StaffDashboard extends Page
{
    protected string $view = 'filament.staff.pages.staff-dashboard';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 0;

    public static function getNavigationLabel(): string { return __('Tableau de bord'); }
    public static function getNavigationGroup(): ?string { return __('Mon espace'); }
    public function getTitle(): string { return __('Mon tableau de bord'); }

    public ?Employee $employee = null;

    public function mount(): void
    {
        $this->employee = auth()->user()?->employee;
    }

    protected function getViewData(): array
    {
        $emp = $this->employee;
        if (! $emp) {
            return ['employee' => null];
        }

        $entries = TimetableEntry::with(['subject', 'classroom.level'])
            ->where('employee_id', $emp->id)->get();

        $todayName = ['Sunday' => 'Dimanche', 'Monday' => 'Lundi', 'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi',
            'Saturday' => 'Samedi'][now()->format('l')] ?? '';

        $today = $entries->where('day_of_week', $todayName)
            ->sortBy('start_time')
            ->map(fn ($e) => [
                'start'   => substr($e->start_time, 0, 5),
                'end'     => substr($e->end_time, 0, 5),
                'subject' => $e->subject?->name ?? '—',
                'class'   => $e->classroom?->name ?? '—',
                'room'    => $e->room,
            ])->values()->toArray();

        $payslips = Payroll::where('employee_id', $emp->id)
            ->orderByDesc('year')->orderByDesc('month')->take(4)->get()
            ->map(fn ($p) => [
                'period' => self::monthName($p->month) . ' ' . $p->year,
                'net'    => (float) $p->net_salary,
                'status' => $p->status,
            ])->toArray();

        return [
            'employee'  => $emp,
            'todayName' => $todayName,
            'stats'     => [
                'sessions' => $entries->count(),
                'hours'    => number_format($entries->sum(fn ($e) => $e->duration_hours ?? 1), 1),
                'classes'  => $entries->pluck('classroom_id')->unique()->count(),
                'subjects' => $entries->pluck('subject_id')->unique()->count(),
            ],
            'today'     => $today,
            'payslips'  => $payslips,
        ];
    }

    protected static function monthName(int $m): string
    {
        return [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'][$m] ?? (string) $m;
    }
}
