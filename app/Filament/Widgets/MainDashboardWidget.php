<?php
namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class MainDashboardWidget extends Widget
{
    protected static bool $isLazy = true;
    protected static ?int $sort = 0;
    protected string $view = 'filament.widgets.main-dashboard-widget';
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $now       = now();
        $month     = $now->month;
        $year      = $now->year;
        $prevMonth = $now->copy()->subMonth();

        // ── KPIs ────────────────────────────────────────────────────────────────
        $totalStudents    = Student::count();
        $activeStudents   = Student::where('status', 'active')->count();
        $newThisMonth     = Student::whereMonth('created_at', $month)->whereYear('created_at', $year)->count();
        $prevActive       = Student::where('status', 'active')
            ->where('created_at', '<', $now->copy()->startOfMonth())->count();
        $studentTrend     = $prevActive > 0 ? round(($activeStudents - $prevActive) / $prevActive * 100, 1) : 0;

        $teachersCount    = Employee::where('is_teacher', true)->count();
        $classesCount     = Classroom::count();

        $totalAtt = Attendance::whereMonth('date', $month)->whereYear('date', $year)->count();
        $presentAtt = Attendance::whereMonth('date', $month)->whereYear('date', $year)
            ->whereIn('status', ['present', 'late'])->count();
        $attendanceRate = $totalAtt > 0 ? round($presentAtt / $totalAtt * 100, 1) : 0;

        $revenueMonth = (float) Payment::where('status', 'paid')
            ->whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('amount');
        $revenuePrev  = (float) Payment::where('status', 'paid')
            ->whereMonth('payment_date', $prevMonth->month)->whereYear('payment_date', $prevMonth->year)->sum('amount');
        $revenueTrend = $revenuePrev > 0 ? round(($revenueMonth - $revenuePrev) / $revenuePrev * 100, 1) : ($revenueMonth > 0 ? 100 : 0);

        $overdueTotal = (float) Payment::where('status', 'pending')
            ->whereNotNull('due_date')->whereDate('due_date', '<', $now)->sum('amount');
        $overdueCount = Payment::where('status', 'pending')
            ->whereNotNull('due_date')->whereDate('due_date', '<', $now)->count();

        // ── Student Evolution chart (6 months) ──────────────────────────────────
        $evolutionMonths = collect(range(5, 0))->map(fn ($i) => $now->copy()->subMonths($i));
        $evolution = [
            'labels'  => $evolutionMonths->map(fn ($m) => ucfirst(Carbon::create($m->year, $m->month)->locale('fr')->isoFormat('MMM')))->toArray(),
            'counts'  => $evolutionMonths->map(fn ($m) =>
                Student::where('created_at', '<=', Carbon::create($m->year, $m->month)->endOfMonth())->count()
            )->toArray(),
        ];

        // ── Class Distribution (by level) ────────────────────────────────────────
        $distribution = Student::where('status', 'active')
            ->with('classroom.level')
            ->get()
            ->groupBy(fn ($s) => $s->classroom?->level?->name ?? 'Sans niveau')
            ->map(fn ($g, $k) => ['label' => $k, 'count' => $g->count()])
            ->values()->toArray();

        // ── Revenue chart (6 months) ─────────────────────────────────────────────
        $revenueChart = [
            'labels'   => $evolutionMonths->map(fn ($m) => ucfirst(Carbon::create($m->year, $m->month)->locale('fr')->isoFormat('MMM')))->toArray(),
            'revenue'  => $evolutionMonths->map(fn ($m) => (float) Payment::where('status','paid')
                ->whereYear('payment_date',$m->year)->whereMonth('payment_date',$m->month)->sum('amount'))->toArray(),
            'expenses' => $evolutionMonths->map(fn ($m) => (float) Expense::whereYear('date',$m->year)
                ->whereMonth('date',$m->month)->sum('amount'))->toArray(),
        ];

        // ── Overdue payments table ────────────────────────────────────────────────
        $overdueTable = Payment::where('status','pending')
            ->whereNotNull('due_date')->whereDate('due_date','<',$now)
            ->with(['student.classroom'])
            ->orderBy('due_date')->take(8)->get()
            ->map(fn ($p) => [
                'student' => $p->student?->full_name ?? '—',
                'class'   => $p->student?->classroom?->name ?? '—',
                'amount'  => $p->amount,
                'days'    => (int) $p->due_date->diffInDays($now),
            ])->toArray();

        // ── Recent activities ─────────────────────────────────────────────────────
        $recentPayments = Payment::where('status','paid')->with('student')
            ->orderByDesc('payment_date')->take(3)->get()
            ->map(fn ($p) => ['type'=>'payment','color'=>'#10b981',
                'text' => 'Paiement reçu — '.($p->student?->full_name ?? '—'),
                'meta' => number_format($p->amount,3).' TND',
                'at'   => $p->payment_date ?? $p->created_at]);

        $recentIncidents = Incident::with('student')
            ->orderByDesc('incident_date')->take(2)->get()
            ->map(fn ($i) => ['type'=>'incident',
                'color' => $i->severity==='high'?'#ef4444':($i->severity==='medium'?'#f59e0b':'#94a3b8'),
                'text'  => 'Incident — '.($i->student?->full_name ?? '—'),
                'meta'  => ucfirst($i->severity),
                'at'    => $i->incident_date ?? $i->created_at]);

        $recentStudents = Student::orderByDesc('created_at')->take(2)->get()
            ->map(fn ($s) => ['type'=>'student','color'=>'#1d4ed8',
                'text' => 'Nouvel élève — '.$s->full_name,
                'meta' => $s->created_at->locale('fr')->diffForHumans(),
                'at'   => $s->created_at]);

        $activities = $recentPayments->concat($recentIncidents)->concat($recentStudents)
            ->sortByDesc('at')->take(6)->values()->toArray();

        // ── Financial summary ────────────────────────────────────────────────────
        $invoiced     = (float) Payment::whereYear('due_date', $year)->sum('amount');
        $received     = (float) Payment::where('status','paid')->whereYear('payment_date', $year)->sum('amount');
        $pending      = (float) Payment::where('status','pending')->whereYear('due_date', $year)->sum('amount');
        $collectRate  = $invoiced > 0 ? round($received / $invoiced * 100, 1) : 0;

        return compact(
            'activeStudents','totalStudents','newThisMonth','studentTrend',
            'teachersCount','classesCount',
            'attendanceRate','totalAtt',
            'revenueMonth','revenueTrend','revenuePrev',
            'overdueTotal','overdueCount',
            'evolution','distribution','revenueChart',
            'overdueTable','activities',
            'invoiced','received','pending','collectRate'
        );
    }
}
