<?php
namespace App\Filament\Parent\Pages;

use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\ReportCardService;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class ChildReportCard extends Page
{
    protected string $view = 'filament.parent.pages.child-report-card';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string { return 'Bulletins'; }
    public function getTitle(): string { return 'Bulletins'; }

    #[Url(as: 'enfant')]
    public ?int $studentId = null;
    public string $term = 'T1';

    public function mount(): void
    {
        $children = $this->childrenOptions();
        if (! $this->studentId || ! array_key_exists($this->studentId, $children)) {
            $this->studentId = array_key_first($children);
        }
    }

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
        // security: only this parent's children
        $report = null;
        if ($this->studentId && array_key_exists($this->studentId, $children)) {
            $student = Student::with('classroom')->find($this->studentId);
            $report = $student ? ReportCardService::forStudent($student, $this->term) : null;
        }

        return [
            'children'   => $children,
            'report'     => $report,
            'schoolName' => SchoolSetting::get('school_name', 'EliteCampus'),
        ];
    }
}
