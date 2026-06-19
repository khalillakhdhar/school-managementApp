<?php
namespace App\Filament\Staff\Pages;

use App\Models\Attendance;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MyAttendance extends Page
{
    protected string $view = 'filament.staff.pages.my-attendance';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-finger-print';
    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string { return __('Mon pointage'); }
    public static function getNavigationGroup(): ?string { return __('Mon espace'); }
    public function getTitle(): string { return __('Mon pointage'); }

    public function clockIn(): void
    {
        $emp = auth()->user()?->employee;
        if (! $emp) {
            return;
        }
        $att = Attendance::firstOrNew(['employee_id' => $emp->id, 'date' => now()->toDateString()]);
        if ($att->time_in) {
            Notification::make()->title(__('Arrivée déjà pointée à :time', ['time' => substr($att->time_in, 0, 5)]))->warning()->send();
            return;
        }
        $att->fill(['status' => 'present', 'time_in' => now()->format('H:i')])->save();
        Notification::make()->title(__('Arrivée pointée à :time', ['time' => now()->format('H:i')]))->success()->send();
    }

    public function clockOut(): void
    {
        $emp = auth()->user()?->employee;
        if (! $emp) {
            return;
        }
        $att = Attendance::firstOrNew(['employee_id' => $emp->id, 'date' => now()->toDateString()]);
        if (! $att->time_in) {
            Notification::make()->title(__('Pointez d\'abord votre arrivée.'))->warning()->send();
            return;
        }
        $hours = round((strtotime(now()->format('H:i')) - strtotime(substr($att->time_in, 0, 5))) / 3600, 2);
        $att->fill(['time_out' => now()->format('H:i'), 'total_hours' => max(0, $hours)])->save();
        Notification::make()->title(__('Départ pointé à :time — :hoursh travaillées', ['time' => now()->format('H:i'), 'hours' => $hours]))->success()->send();
    }

    protected function getViewData(): array
    {
        $emp = auth()->user()?->employee;
        if (! $emp) {
            return ['today' => null, 'rows' => collect(), 'stats' => []];
        }

        $today = Attendance::where('employee_id', $emp->id)->whereDate('date', now())->first();

        $month = now()->month;
        $year = now()->year;
        $rows = Attendance::where('employee_id', $emp->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->orderByDesc('date')->get()
            ->map(fn ($a) => [
                'date'   => $a->date->locale('fr')->isoFormat('ddd D MMM'),
                'status' => $a->status,
                'in'     => $a->time_in ? substr($a->time_in, 0, 5) : '—',
                'out'    => $a->time_out ? substr($a->time_out, 0, 5) : '—',
                'hours'  => $a->total_hours ? rtrim(rtrim((string) $a->total_hours, '0'), '.') . 'h' : '—',
            ]);

        $present = Attendance::where('employee_id', $emp->id)->whereMonth('date', $month)->whereYear('date', $year)
            ->whereIn('status', ['present', 'late'])->count();
        $absent = Attendance::where('employee_id', $emp->id)->whereMonth('date', $month)->whereYear('date', $year)
            ->where('status', 'absent')->count();

        return [
            'today' => $today ? ['in' => $today->time_in ? substr($today->time_in, 0, 5) : null,
                                 'out' => $today->time_out ? substr($today->time_out, 0, 5) : null] : null,
            'rows'  => $rows,
            'stats' => ['present' => $present, 'absent' => $absent, 'days' => $rows->count()],
        ];
    }
}
