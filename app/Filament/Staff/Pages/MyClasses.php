<?php
namespace App\Filament\Staff\Pages;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TimetableEntry;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class MyClasses extends Page
{
    protected string $view = 'filament.staff.pages.my-classes';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string { return 'Mes classes'; }
    public static function getNavigationGroup(): ?string { return 'Enseignement'; }
    public function getTitle(): string { return 'Mes classes'; }

    /** Null = list view ; set = roster of that class */
    #[Url(as: 'classe')]
    public ?int $classroomId = null;

    public function selectClass(int $id): void { $this->classroomId = $id; }
    public function backToList(): void { $this->classroomId = null; }

    /** Classroom IDs the teacher intervenes in (titulaire OR via timetable). */
    protected function myClassroomIds(): Collection
    {
        $emp = auth()->user()?->employee;
        if (! $emp) {
            return collect();
        }
        return TimetableEntry::where('employee_id', $emp->id)->pluck('classroom_id')
            ->merge($emp->classrooms()->pluck('id'))
            ->unique()->filter()->values();
    }

    protected function getViewData(): array
    {
        $emp = auth()->user()?->employee;
        $ids = $this->myClassroomIds();
        $month = now()->month;
        $year = now()->year;

        if ($this->classroomId && $ids->contains($this->classroomId)) {
            $class = Classroom::with('level')->find($this->classroomId);
            $students = Student::where('classroom_id', $this->classroomId)
                ->orderBy('last_name')->orderBy('first_name')->get()
                ->map(function ($s) use ($month, $year) {
                    $total = StudentAttendance::where('student_id', $s->id)
                        ->whereMonth('date', $month)->whereYear('date', $year)->count();
                    $present = StudentAttendance::where('student_id', $s->id)
                        ->whereMonth('date', $month)->whereYear('date', $year)
                        ->whereIn('status', ['present', 'late'])->count();
                    return [
                        'name'   => $s->full_name,
                        'id_num' => $s->id_number,
                        'status' => $s->status,
                        'rate'   => $total > 0 ? round($present / $total * 100) : null,
                    ];
                });
            return ['view' => 'detail', 'class' => $class, 'students' => $students];
        }

        // list view — cards of my classes
        $classes = Classroom::with(['level'])->whereIn('id', $ids)->orderBy('level_id')->get()
            ->map(function ($c) use ($emp) {
                $subjects = TimetableEntry::where('classroom_id', $c->id)
                    ->where('employee_id', $emp?->id)
                    ->with('subject')->get()->pluck('subject.name')->filter()->unique()->values();
                return [
                    'id'         => $c->id,
                    'name'       => $c->name,
                    'full'       => $c->full_name,
                    'level'      => $c->level?->name,
                    'students'   => Student::where('classroom_id', $c->id)->count(),
                    'subjects'   => $subjects->implode(', '),
                    'titulaire'  => $c->teacher_id === $emp?->id,
                ];
            });

        return ['view' => 'list', 'classes' => $classes];
    }
}
