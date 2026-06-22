<?php
namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\TimetableEntry;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class TeacherSchedule extends Page
{
    protected string $view = 'filament.pages.teacher-schedule';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string { return __('Académique'); }
    public static function getNavigationLabel(): string  { return __('Planning enseignants'); }

    public function getTitle(): string
    {
        if ($this->employeeId && ($t = $this->selectedTeacher)) {
            return __('Planning — ') . $t->full_name;
        }
        return __('Planning enseignants');
    }

    /** Null = list view; set = detail view for that teacher */
    #[Url(as: 'enseignant')]
    public ?int $employeeId = null;

    public function selectTeacher(int $id): void
    {
        $this->employeeId = $id;
    }

    public function backToList(): void
    {
        $this->employeeId = null;
    }

    // ── Computed: selected teacher model ───────────────────────────────────

    public function getSelectedTeacherProperty(): ?Employee
    {
        if (!$this->employeeId) return null;
        return Employee::find($this->employeeId);
    }

    // ── Computed: list view ─────────────────────────────────────────────────

    public function getTeachersListProperty(): \Illuminate\Support\Collection
    {
        return Employee::active()->teachers()
            ->with(['timetableEntries.subject', 'timetableEntries.classroom.level'])
            ->orderBy('last_name')
            ->get()
            ->map(function ($t) {
                $entries  = $t->timetableEntries;
                $classes  = $entries->pluck('classroom.full_name')->filter()->unique()->values();
                $subjects = $entries->pluck('subject.name')->filter()->unique()->values();
                return [
                    'id'       => $t->id,
                    'name'     => $t->full_name,
                    'position' => $t->specialite ?? $t->position ?? '',
                    'sessions' => $entries->count(),
                    'hours'    => number_format($entries->sum(fn ($e) => $e->duration_hours), 1),
                    'classes'  => $classes->take(3)->implode(', ')  . ($classes->count()  > 3 ? ' +' . ($classes->count()  - 3) : ''),
                    'subjects' => $subjects->take(2)->implode(', ') . ($subjects->count() > 2 ? ' +' . ($subjects->count() - 2) : ''),
                    'hasData'  => $entries->count() > 0,
                ];
            });
    }

    // ── Computed: detail view KPIs ─────────────────────────────────────────

    public function getDetailStatsProperty(): array
    {
        if (!$this->employeeId) return [];

        $entries = TimetableEntry::query()
            ->with(['subject', 'classroom.level'])
            ->where('employee_id', $this->employeeId)
            ->get();

        return [
            ['label' => __('Séances / semaine'),  'value' => $entries->count(),
             'color' => '#7c3aed', 'bg' => '#f5f3ff'],
            ['label' => __('Heures / semaine'),    'value' => number_format($entries->sum(fn ($e) => $e->duration_hours), 1) . 'h',
             'color' => '#059669', 'bg' => '#ecfdf5'],
            ['label' => __('Classes enseignées'),  'value' => $entries->pluck('classroom_id')->unique()->count(),
             'color' => '#1d4ed8', 'bg' => '#eff6ff'],
            ['label' => __('Matières'),            'value' => $entries->pluck('subject_id')->unique()->count(),
             'color' => '#d97706', 'bg' => '#fffbeb'],
        ];
    }

    // ── Computed: timetable grid (time slots × days) ───────────────────────

    public function getTimetableDataProperty(): array
    {
        if (!$this->employeeId) {
            return ['slots' => [], 'grid' => [], 'empty' => true];
        }

        $entries = TimetableEntry::query()
            ->with(['subject', 'classroom.level'])
            ->where('employee_id', $this->employeeId)
            ->get();

        if ($entries->isEmpty()) {
            return ['slots' => [], 'grid' => [], 'empty' => true];
        }

        $slots = $entries
            ->map(fn ($e) => [
                'start' => substr($e->start_time, 0, 5),
                'end'   => substr($e->end_time,   0, 5),
            ])
            ->unique('start')
            ->sortBy('start')
            ->values()
            ->toArray();

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

        return ['slots' => $slots, 'grid' => $grid, 'empty' => false];
    }
}
