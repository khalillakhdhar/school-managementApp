<?php
namespace App\Filament\Widgets;

use App\Models\Classroom;
use App\Models\Incident;
use App\Models\Payment;
use Filament\Widgets\Widget;

class SmartAlertsWidget extends Widget
{
    protected static bool $isLazy = true;
    protected static ?int $sort = 4;
    protected string $view = 'filament.widgets.smart-alerts-widget';

    public function getColumnSpan(\Filament\Support\Enums\MaxWidth|int|string|null $maxWidth = null): int|string
    {
        return $this->hasAlerts() ? 'full' : 1;
    }

    private function hasAlerts(): bool
    {
        return Payment::where('status', 'pending')
                ->whereNotNull('due_date')->whereDate('due_date', '<', now())->exists()
            || Incident::where('parent_notified', false)->exists()
            || Classroom::whereNull('teacher_id')->exists();
    }

    protected function getViewData(): array
    {
        $overduePayments = Payment::where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->with('student')
            ->orderBy('due_date')
            ->take(5)->get();

        $unnotifiedIncidents = Incident::where('parent_notified', false)
            ->with('student')
            ->orderByDesc('incident_date')
            ->take(5)->get();

        $overdueCount     = Payment::where('status', 'pending')
            ->whereNotNull('due_date')->whereDate('due_date', '<', now())->count();
        $unnotifiedCount  = Incident::where('parent_notified', false)->count();
        $classesNoTeacher = Classroom::whereNull('teacher_id')->count();

        return [
            'overduePayments'     => $overduePayments,
            'overdueCount'        => $overdueCount,
            'overdueTotal'        => (float) Payment::where('status', 'pending')
                ->whereNotNull('due_date')->whereDate('due_date', '<', now())->sum('amount'),
            'unnotifiedIncidents' => $unnotifiedIncidents,
            'unnotifiedCount'     => $unnotifiedCount,
            'classesNoTeacher'    => $classesNoTeacher,
        ];
    }
}
