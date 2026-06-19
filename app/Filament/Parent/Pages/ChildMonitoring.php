<?php
namespace App\Filament\Parent\Pages;

use App\Models\Incident;
use App\Models\StudentAttendance;
use Filament\Pages\Page;

class ChildMonitoring extends Page
{
    protected string $view = 'filament.parent.pages.child-monitoring';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string { return __('Suivi'); }
    public function getTitle(): string { return __("Suivi (présences & incidents)"); }

    protected function getViewData(): array
    {
        $parent = auth()->user()?->parent;
        if (! $parent) {
            return ['children' => []];
        }

        $month = now()->month;
        $year = now()->year;

        $children = $parent->students()->with('classroom')->get()->map(function ($student) use ($month, $year) {
            $total = StudentAttendance::where('student_id', $student->id)
                ->whereMonth('date', $month)->whereYear('date', $year)->count();
            $present = StudentAttendance::where('student_id', $student->id)
                ->whereMonth('date', $month)->whereYear('date', $year)->whereIn('status', ['present', 'late'])->count();
            $absent = StudentAttendance::where('student_id', $student->id)
                ->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count();
            $late = StudentAttendance::where('student_id', $student->id)
                ->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'late')->count();

            $incidents = Incident::where('student_id', $student->id)
                ->orderByDesc('incident_date')->take(8)->get()
                ->map(fn ($i) => [
                    'title'    => $i->title,
                    'type'     => $i->type,
                    'severity' => $i->severity,
                    'date'     => $i->incident_date?->format('d/m/Y') ?? '—',
                ])->toArray();

            return [
                'name'    => $student->full_name,
                'class'   => $student->classroom?->name ?? '—',
                'rate'    => $total > 0 ? round($present / $total * 100) : null,
                'present' => $present,
                'absent'  => $absent,
                'late'    => $late,
                'incidents' => $incidents,
            ];
        })->toArray();

        return ['children' => $children];
    }
}
