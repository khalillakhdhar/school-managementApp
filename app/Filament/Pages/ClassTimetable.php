<?php
namespace App\Filament\Pages;

use App\Models\Classroom;
use App\Models\TimetableEntry;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class ClassTimetable extends Page
{
    protected string $view = 'filament.pages.class-timetable';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';
    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string { return __('Académique'); }
    public static function getNavigationLabel(): string  { return __('Emploi du temps'); }

    public function getTitle(): string
    {
        if ($this->classroomId && ($c = $this->selectedClassroom)) {
            return __('EDT — ') . $c->full_name;
        }
        return __('Emplois du temps');
    }

    /** Null = list view; set = detail view for that class */
    #[Url(as: 'classe')]
    public ?int $classroomId = null;

    public function selectClass(int $id): void
    {
        $this->classroomId = $id;
    }

    public function backToList(): void
    {
        $this->classroomId = null;
    }

    // ── Computed: selected classroom model ─────────────────────────────────

    public function getSelectedClassroomProperty(): ?Classroom
    {
        if (!$this->classroomId) return null;
        return Classroom::with(['level', 'teacher'])->find($this->classroomId);
    }

    // ── Computed: list view ─────────────────────────────────────────────────

    public function getClassroomsListProperty(): \Illuminate\Support\Collection
    {
        return Classroom::with(['level', 'teacher', 'timetableEntries'])
            ->orderBy('level_id')
            ->get()
            ->map(function ($c) {
                $entries = $c->timetableEntries;
                return [
                    'id'      => $c->id,
                    'name'    => $c->name,
                    'full'    => $c->full_name,
                    'level'   => $c->level?->name ?? '',
                    'code'    => $c->level?->code ?? '?',
                    'teacher' => $c->teacher?->full_name,
                    'sessions'=> $entries->count(),
                    'hours'   => number_format($entries->sum(fn ($e) => $e->duration_hours), 1),
                    'subjects'=> $entries->pluck('subject_id')->unique()->count(),
                    'hasData' => $entries->count() > 0,
                ];
            });
    }

    // ── Computed: detail view KPIs ─────────────────────────────────────────

    public function getDetailStatsProperty(): array
    {
        if (!$this->classroomId) return [];

        $entries = TimetableEntry::query()
            ->with(['subject', 'teacher'])
            ->where('classroom_id', $this->classroomId)
            ->get();

        return [
            ['label' => 'Séances / semaine',  'value' => $entries->count(),
             'color' => '#1d4ed8', 'bg' => '#eff6ff'],
            ['label' => 'Heures / semaine',    'value' => number_format($entries->sum(fn ($e) => $e->duration_hours), 1) . 'h',
             'color' => '#059669', 'bg' => '#ecfdf5'],
            ['label' => 'Matières enseignées', 'value' => $entries->pluck('subject_id')->unique()->count(),
             'color' => '#7c3aed', 'bg' => '#f5f3ff'],
            ['label' => 'Enseignants actifs',  'value' => $entries->pluck('employee_id')->filter()->unique()->count(),
             'color' => '#d97706', 'bg' => '#fffbeb'],
        ];
    }

    // ── Computed: timetable grid (time slots × days) ───────────────────────

    public function getTimetableDataProperty(): array
    {
        if (!$this->classroomId) {
            return ['slots' => [], 'grid' => [], 'empty' => true];
        }

        $entries = TimetableEntry::query()
            ->with(['subject', 'teacher'])
            ->where('classroom_id', $this->classroomId)
            ->get();

        if ($entries->isEmpty()) {
            return ['slots' => [], 'grid' => [], 'empty' => true];
        }

        // Unique time slots sorted chronologically — become table columns
        $slots = $entries
            ->map(fn ($e) => [
                'start' => substr($e->start_time, 0, 5),
                'end'   => substr($e->end_time,   0, 5),
            ])
            ->unique('start')
            ->sortBy('start')
            ->values()
            ->toArray();

        // Fast lookup: day → start_time → TimetableEntry model
        $index = [];
        foreach ($entries as $e) {
            $index[$e->day_of_week][substr($e->start_time, 0, 5)] = $e;
        }

        // One row per day, cells ordered by slot
        $grid = [];
        foreach (TimetableEntry::$days as $day) {
            $grid[] = [
                'day'   => $day,
                'cells' => array_map(fn ($s) => $index[$day][$s['start']] ?? null, $slots),
            ];
        }

        return ['slots' => $slots, 'grid' => $grid, 'empty' => false];
    }
}
