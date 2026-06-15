<?php
namespace App\Filament\Pages;

use App\Models\Classroom;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\ReportCardService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ReportCards extends Page
{
    protected string $view = 'filament.pages.report-cards';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string { return 'Académique'; }
    public static function getNavigationLabel(): string  { return 'Bulletins'; }
    public function getTitle(): string { return 'Bulletins scolaires'; }

    public ?int $classroomId = null;
    public ?int $studentId = null;
    public string $term = 'T1';

    public function mount(): void
    {
        $this->classroomId = Classroom::orderBy('level_id')->value('id');
        $this->studentId = Student::where('classroom_id', $this->classroomId)->value('id');
    }

    public function updatedClassroomId(): void
    {
        $this->studentId = Student::where('classroom_id', $this->classroomId)->value('id');
    }

    public function classrooms(): Collection
    {
        return Classroom::with('level')->orderBy('level_id')->get()->mapWithKeys(fn ($c) => [$c->id => $c->full_name]);
    }

    public function students(): Collection
    {
        if (! $this->classroomId) {
            return collect();
        }
        return Student::where('classroom_id', $this->classroomId)->orderBy('last_name')->get()
            ->mapWithKeys(fn ($s) => [$s->id => $s->full_name]);
    }

    protected function getViewData(): array
    {
        $student = $this->studentId ? Student::with('classroom')->find($this->studentId) : null;
        $report = $student ? ReportCardService::forStudent($student, $this->term) : null;

        return [
            'report'     => $report,
            'schoolName' => SchoolSetting::get('school_name', 'EliteCampus'),
        ];
    }
}
