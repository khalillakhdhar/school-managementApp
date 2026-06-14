<?php
namespace App\Filament\Widgets;

use App\Models\Incident;
use App\Models\Payment;
use App\Models\Student;
use Filament\Widgets\Widget;

class DashboardHeaderWidget extends Widget
{
    protected static bool $isLazy = false;
    protected static ?int $sort = -2;
    protected string $view = 'filament.widgets.dashboard-header-widget';
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $month = now()->month;
        $year  = now()->year;

        return [
            'userName'       => auth()->user()?->name ?? 'Admin',
            'schoolYear'     => $month >= 9 ? "{$year}–" . ($year + 1) : ($year - 1) . "–{$year}",
            'activeStudents' => Student::where('status', 'active')->count(),
            'totalStudents'  => Student::count(),
            'revenueMonth'   => (float) Payment::where('status', 'paid')
                ->whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('amount'),
            'overdueCount'   => Payment::where('status', 'pending')
                ->whereNotNull('due_date')->whereDate('due_date', '<', now())->count(),
            'pendingIncidents' => Incident::where('parent_notified', false)->count(),
        ];
    }
}
