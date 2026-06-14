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
    protected static string $view = 'filament.widgets.smart-alerts-widget';
    protected int|string|array $columnSpan = 'full';

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

        return [
            'overduePayments'     => $overduePayments,
            'overdueCount'        => Payment::where('status', 'pending')
                ->whereNotNull('due_date')->whereDate('due_date', '<', now())->count(),
            'overdueTotal'        => (float) Payment::where('status', 'pending')
                ->whereNotNull('due_date')->whereDate('due_date', '<', now())->sum('amount'),
            'unnotifiedIncidents' => $unnotifiedIncidents,
            'unnotifiedCount'     => Incident::where('parent_notified', false)->count(),
            'classesNoTeacher'    => Classroom::whereNull('teacher_id')->count(),
        ];
    }
}
