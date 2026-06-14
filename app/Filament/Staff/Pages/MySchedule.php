<?php
namespace App\Filament\Staff\Pages;

use App\Models\TimetableEntry;
use Filament\Pages\Page;

class MySchedule extends Page
{
    protected string $view = 'filament.staff.pages.my-schedule';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string { return 'Mon emploi du temps'; }
    public static function getNavigationGroup(): ?string { return 'Enseignement'; }
    public function getTitle(): string { return 'Mon emploi du temps'; }

    protected function getViewData(): array
    {
        $emp = auth()->user()?->employee;
        if (! $emp) {
            return ['empty' => true, 'slots' => [], 'grid' => []];
        }

        $entries = TimetableEntry::with(['subject', 'classroom.level'])
            ->where('employee_id', $emp->id)->get();

        if ($entries->isEmpty()) {
            return ['empty' => true, 'slots' => [], 'grid' => []];
        }

        $slots = $entries->map(fn ($e) => [
            'start' => substr($e->start_time, 0, 5),
            'end'   => substr($e->end_time, 0, 5),
        ])->unique('start')->sortBy('start')->values()->toArray();

        $index = [];
        foreach ($entries as $e) {
            $index[$e->day_of_week][substr($e->start_time, 0, 5)] = $e;
        }

        $grid = [];
        foreach (TimetableEntry::$days as $day) {
            $grid[] = [
                'day'   => $day,
                'cells' => array_map(fn ($s) => $index[$day][$s['start']] ?? null, $slots),
            ];
        }

        return ['empty' => false, 'slots' => $slots, 'grid' => $grid];
    }
}
