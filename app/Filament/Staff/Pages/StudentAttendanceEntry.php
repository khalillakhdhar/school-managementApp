<?php
namespace App\Filament\Staff\Pages;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TimetableEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class StudentAttendanceEntry extends Page
{
    protected string $view = 'filament.staff.pages.student-attendance-entry';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string { return "Faire l'appel"; }
    public static function getNavigationGroup(): ?string { return 'Enseignement'; }
    public function getTitle(): string { return "Faire l'appel des élèves"; }

    public ?int $classroomId = null;
    public string $date = '';
    /** @var array<int,string> student_id => status */
    public array $statuses = [];

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $classes = $this->myClasses();
        $this->classroomId = $classes->first()['id'] ?? null;
        $this->loadRoster();
    }

    public function updatedClassroomId(): void { $this->loadRoster(); }
    public function updatedDate(): void { $this->loadRoster(); }

    /** Classes the logged-in teacher may take attendance for. */
    public function myClasses(): Collection
    {
        $emp = auth()->user()?->employee;
        if (! $emp) {
            return collect();
        }

        $ids = TimetableEntry::where('employee_id', $emp->id)->pluck('classroom_id')
            ->merge($emp->classrooms()->pluck('id'))
            ->unique()->filter()->values();

        return Classroom::with('level')->whereIn('id', $ids)->orderBy('level_id')->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'full' => $c->full_name]);
    }

    public function getStudentsProperty(): Collection
    {
        if (! $this->classroomId) {
            return collect();
        }
        return Student::where('classroom_id', $this->classroomId)
            ->orderBy('last_name')->orderBy('first_name')->get();
    }

    /** Pre-fill statuses (default present), overriding with any saved record. */
    public function loadRoster(): void
    {
        $this->statuses = [];
        foreach ($this->students as $student) {
            $this->statuses[$student->id] = 'present';
        }
        if ($this->classroomId && $this->date) {
            $existing = StudentAttendance::where('classroom_id', $this->classroomId)
                ->whereDate('date', $this->date)->pluck('status', 'student_id');
            foreach ($existing as $sid => $status) {
                $this->statuses[$sid] = $status;
            }
        }
    }

    public function setStatus(int $studentId, string $status): void
    {
        $this->statuses[$studentId] = $status;
    }

    public function markAll(string $status): void
    {
        foreach (array_keys($this->statuses) as $sid) {
            $this->statuses[$sid] = $status;
        }
    }

    public function getSummaryProperty(): array
    {
        $vals = array_count_values($this->statuses);
        return [
            'present' => $vals['present'] ?? 0,
            'absent'  => $vals['absent'] ?? 0,
            'late'    => $vals['late'] ?? 0,
            'excused' => $vals['excused'] ?? 0,
        ];
    }

    public function save(): void
    {
        $emp = auth()->user()?->employee;
        if (! $this->classroomId || ! $emp) {
            return;
        }

        foreach ($this->statuses as $studentId => $status) {
            StudentAttendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $this->date],
                [
                    'classroom_id' => $this->classroomId,
                    'employee_id'  => $emp->id,
                    'status'       => $status,
                ],
            );
        }

        Notification::make()
            ->title("Appel enregistré — " . count($this->statuses) . " élève(s)")
            ->success()->send();
    }
}
