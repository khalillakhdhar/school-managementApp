<?php
namespace App\Filament\Parent\Pages;

use App\Models\Student;
use App\Models\TimetableEntry;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class ChildTimetable extends Page
{
    protected string $view = 'filament.parent.pages.child-timetable';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string { return __('Emploi du temps'); }
    public function getTitle(): string { return __("Emploi du temps"); }

    #[Url(as: 'enfant')]
    public ?int $studentId = null;

    public function mount(): void
    {
        $children = $this->childrenOptions();
        if (! $this->studentId || ! array_key_exists($this->studentId, $children)) {
            $this->studentId = array_key_first($children);
        }
    }

    /** @return array<int,string> safe list of THIS parent's children */
    public function childrenOptions(): array
    {
        $parent = auth()->user()?->parent;
        if (! $parent) {
            return [];
        }
        return $parent->students()->get()->mapWithKeys(fn ($s) => [$s->id => $s->full_name])->toArray();
    }

    protected function getViewData(): array
    {
        $children = $this->childrenOptions();
        // security: only allow a child that belongs to this parent
        if (! $this->studentId || ! array_key_exists($this->studentId, $children)) {
            return ['children' => $children, 'empty' => true, 'slots' => [], 'grid' => [], 'className' => null];
        }

        $student = Student::with('classroom')->find($this->studentId);
        $classroomId = $student?->classroom_id;
        $entries = $classroomId
            ? TimetableEntry::with(['subject', 'teacher'])->where('classroom_id', $classroomId)->get()
            : collect();

        if ($entries->isEmpty()) {
            return ['children' => $children, 'empty' => true, 'slots' => [], 'grid' => [], 'className' => $student?->classroom?->name];
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
            $grid[] = ['day' => $day, 'cells' => array_map(fn ($s) => $index[$day][$s['start']] ?? null, $slots)];
        }

        return ['children' => $children, 'empty' => false, 'slots' => $slots, 'grid' => $grid, 'className' => $student?->classroom?->name];
    }
}
