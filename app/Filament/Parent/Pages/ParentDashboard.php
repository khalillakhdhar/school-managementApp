<?php
namespace App\Filament\Parent\Pages;

use App\Models\BlogPost;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\SchoolParent;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Filament\Pages\Page;

class ParentDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Mon Tableau de Bord';
    protected static ?int $navigationSort = 0;

    public function getView(): string { return 'filament.parent.pages.parent-dashboard'; }

    public ?SchoolParent $schoolParent = null;

    public function mount(): void
    {
        $this->schoolParent = SchoolParent::where('user_id', auth()->id())->first();
    }

    protected function getViewData(): array
    {
        $parent = $this->schoolParent;
        if (! $parent) {
            return ['parent' => null];
        }

        $now = now();
        $month = $now->month;
        $year = $now->year;
        $students = $parent->students()->with('classroom')->get();
        $studentIds = $students->pluck('id');

        // ── Per-child summary ────────────────────────────────────────────────
        $children = $students->map(function ($s) use ($month, $year, $now) {
            $total = StudentAttendance::where('student_id', $s->id)->whereMonth('date', $month)->whereYear('date', $year)->count();
            $present = StudentAttendance::where('student_id', $s->id)->whereMonth('date', $month)->whereYear('date', $year)->whereIn('status', ['present', 'late'])->count();
            $outstanding = (float) Payment::where('student_id', $s->id)->where('status', 'pending')->sum('amount');
            return [
                'name'        => $s->full_name,
                'class'       => $s->classroom?->name ?? '—',
                'rate'        => $total > 0 ? round($present / $total * 100) : null,
                'outstanding' => $outstanding,
                'incidents'   => Incident::where('student_id', $s->id)->whereMonth('incident_date', $month)->whereYear('incident_date', $year)->count(),
            ];
        });

        // ── KPIs ─────────────────────────────────────────────────────────────
        $totalOutstanding = (float) Payment::whereIn('student_id', $studentIds)->where('status', 'pending')->sum('amount');
        $attTotal = StudentAttendance::whereIn('student_id', $studentIds)->whereMonth('date', $month)->whereYear('date', $year)->count();
        $attPresent = StudentAttendance::whereIn('student_id', $studentIds)->whereMonth('date', $month)->whereYear('date', $year)->whereIn('status', ['present', 'late'])->count();
        $avgAttendance = $attTotal > 0 ? round($attPresent / $attTotal * 100, 1) : 0;
        $incidentsMonth = Incident::whereIn('student_id', $studentIds)->whereMonth('incident_date', $month)->whereYear('incident_date', $year)->count();

        $nextDue = Payment::whereIn('student_id', $studentIds)->where('status', 'pending')
            ->whereNotNull('due_date')->orderBy('due_date')->first();

        // ── Attendance trend (6 months, aggregate) ───────────────────────────
        $trendMonths = collect(range(5, 0))->map(fn ($i) => $now->copy()->subMonths($i));
        $attendanceTrend = [
            'labels' => $trendMonths->map(fn ($m) => ucfirst(Carbon::create($m->year, $m->month)->locale(app()->getLocale())->isoFormat('MMM')))->toArray(),
            'rates'  => $trendMonths->map(function ($m) use ($studentIds) {
                $t = StudentAttendance::whereIn('student_id', $studentIds)->whereMonth('date', $m->month)->whereYear('date', $m->year)->count();
                $p = StudentAttendance::whereIn('student_id', $studentIds)->whereMonth('date', $m->month)->whereYear('date', $m->year)->whereIn('status', ['present', 'late'])->count();
                return $t > 0 ? round($p / $t * 100, 1) : 0;
            })->toArray(),
        ];

        // ── Payment breakdown (donut) ────────────────────────────────────────
        $paid = (float) Payment::whereIn('student_id', $studentIds)->where('status', 'paid')->sum('amount');
        $overdue = (float) Payment::whereIn('student_id', $studentIds)->where('status', 'pending')->whereDate('due_date', '<', $now)->sum('amount');
        $pending = max(0, $totalOutstanding - $overdue);
        $paymentBreakdown = ['paid' => $paid, 'pending' => $pending, 'overdue' => $overdue];

        // ── Recent activity ──────────────────────────────────────────────────
        $recentPayments = Payment::whereIn('student_id', $studentIds)->where('status', 'paid')->with('student')
            ->orderByDesc('payment_date')->take(3)->get()
            ->map(fn ($p) => ['color' => '#10b981', 'icon' => '💳',
                'text' => __('Paiement reçu — :name', ['name' => $p->student?->first_name ?? '']),
                'meta' => number_format((float) $p->amount, 3) . ' TND', 'at' => $p->payment_date ?? $p->created_at]);
        $recentIncidents = Incident::whereIn('student_id', $studentIds)->with('student')
            ->orderByDesc('incident_date')->take(3)->get()
            ->map(fn ($i) => ['color' => $i->severity === 'high' ? '#ef4444' : ($i->severity === 'medium' ? '#f59e0b' : '#94a3b8'), 'icon' => '⚠️',
                'text' => $i->title . ' — ' . ($i->student?->first_name ?? ''),
                'meta' => ucfirst($i->type), 'at' => $i->incident_date ?? $i->created_at]);
        $activities = $recentPayments->concat($recentIncidents)->sortByDesc('at')->take(6)
            ->map(fn ($a) => ['color' => $a['color'], 'icon' => $a['icon'], 'text' => $a['text'], 'meta' => $a['meta'],
                'ago' => Carbon::parse($a['at'])->locale(app()->getLocale())->diffForHumans()])->values()->toArray();

        // ── Announcements ────────────────────────────────────────────────────
        $announcements = BlogPost::where('is_published', true)->orderByDesc('published_at')->take(3)->get()
            ->map(fn ($p) => ['title' => $p->title, 'date' => $p->published_at?->locale(app()->getLocale())->isoFormat('D MMM') ?? ''])->toArray();

        return [
            'parent'           => $parent,
            'childrenCount'    => $students->count(),
            'children'         => $children->toArray(),
            'totalOutstanding' => $totalOutstanding,
            'avgAttendance'    => $avgAttendance,
            'incidentsMonth'   => $incidentsMonth,
            'nextDue'          => $nextDue ? ['amount' => (float) $nextDue->amount, 'date' => $nextDue->due_date?->format('d/m/Y')] : null,
            'attendanceTrend'  => $attendanceTrend,
            'paymentBreakdown' => $paymentBreakdown,
            'activities'       => $activities,
            'announcements'    => $announcements,
        ];
    }
}
