<?php
namespace App\Filament\Staff\Pages;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TimetableEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class GradeEntry extends Page
{
    protected string $view = 'filament.staff.pages.grade-entry';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string { return 'Saisie des notes'; }
    public static function getNavigationGroup(): ?string { return 'Enseignement'; }
    public function getTitle(): string { return 'Saisie des notes'; }

    public ?int $classroomId = null;
    public ?int $subjectId = null;
    public string $term = 'T1';
    /** @var array<int,string|null> student_id => score */
    public array $scores = [];

    public function mount(): void
    {
        $classes = $this->myClasses();
        $this->classroomId = $classes->keys()->first();
        $subjects = $this->subjectsForClass();
        $this->subjectId = $subjects->keys()->first();
        $this->loadGrades();
    }

    public function updatedClassroomId(): void
    {
        $subjects = $this->subjectsForClass();
        $this->subjectId = $subjects->keys()->first();
        $this->loadGrades();
    }
    public function updatedSubjectId(): void { $this->loadGrades(); }
    public function updatedTerm(): void { $this->loadGrades(); }

    /** @return Collection<int,string> id => full name */
    public function myClasses(): Collection
    {
        $emp = auth()->user()?->employee;
        if (! $emp) {
            return collect();
        }
        $ids = TimetableEntry::where('employee_id', $emp->id)->pluck('classroom_id')->unique()->filter();
        return Classroom::with('level')->whereIn('id', $ids)->orderBy('level_id')->get()
            ->mapWithKeys(fn ($c) => [$c->id => $c->full_name]);
    }

    /** Subjects this teacher teaches in the selected class. */
    public function subjectsForClass(): Collection
    {
        $emp = auth()->user()?->employee;
        if (! $emp || ! $this->classroomId) {
            return collect();
        }
        $ids = TimetableEntry::where('employee_id', $emp->id)
            ->where('classroom_id', $this->classroomId)->pluck('subject_id')->unique()->filter();
        return Subject::whereIn('id', $ids)->orderBy('name')->get()
            ->mapWithKeys(fn ($s) => [$s->id => $s->name]);
    }

    public function getStudentsProperty(): Collection
    {
        if (! $this->classroomId) {
            return collect();
        }
        return Student::where('classroom_id', $this->classroomId)
            ->orderBy('last_name')->orderBy('first_name')->get();
    }

    public function loadGrades(): void
    {
        $this->scores = [];
        if (! $this->classroomId || ! $this->subjectId) {
            return;
        }
        $existing = Grade::where('classroom_id', $this->classroomId)
            ->where('subject_id', $this->subjectId)->where('term', $this->term)
            ->pluck('score', 'student_id');
        foreach ($this->students as $s) {
            $this->scores[$s->id] = $existing[$s->id] ?? null;
        }
    }

    public function save(): void
    {
        $emp = auth()->user()?->employee;
        if (! $emp || ! $this->classroomId || ! $this->subjectId) {
            return;
        }

        // SÉCURITÉ : l'enseignant ne peut noter qu'une matière qu'il enseigne dans CETTE classe.
        if (! $this->myClasses()->keys()->contains($this->classroomId)
            || ! $this->subjectsForClass()->keys()->contains($this->subjectId)) {
            Notification::make()->title(__('Accès refusé à cette classe/matière.'))->danger()->send();
            return;
        }
        if (! in_array($this->term, ['T1', 'T2', 'T3'], true)) {
            return;
        }

        $coef = (float) (Subject::find($this->subjectId)?->coefficient ?? 1);
        // SÉCURITÉ : seuls les élèves réellement inscrits dans cette classe.
        $rosterIds = Student::where('classroom_id', $this->classroomId)->pluck('id')->flip();
        $saved = 0;

        foreach ($this->scores as $studentId => $score) {
            if ($score === null || $score === '' || ! $rosterIds->has($studentId)) {
                continue;
            }
            $score = max(0, min(20, (float) $score));
            Grade::updateOrCreate(
                ['student_id' => $studentId, 'subject_id' => $this->subjectId, 'term' => $this->term],
                [
                    'classroom_id' => $this->classroomId,
                    'employee_id'  => $emp->id,
                    'score'        => $score,
                    'max_score'    => 20,
                    'coefficient'  => $coef,
                    'date'         => now(),
                ],
            );
            $saved++;
        }

        Notification::make()->title("{$saved} note(s) enregistrée(s)")->success()->send();
    }
}
