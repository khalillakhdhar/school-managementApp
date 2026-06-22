<?php
namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Pages\Page;

class FinancialReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 1;

    public string  $period = 'month';
    public ?string $from   = null;
    public ?string $until  = null;

    public static function getNavigationLabel(): string  { return __('Rapport Financier'); }
    public static function getNavigationGroup(): ?string { return __('Finances'); }
    public function getTitle(): string                   { return __('Rapport Financier'); }
    public function getView(): string                    { return 'filament.pages.financial-report'; }

    public function mount(): void
    {
        $this->from  = now()->startOfMonth()->toDateString();
        $this->until = now()->endOfMonth()->toDateString();
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->from   = match ($period) {
            'month'   => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year'    => now()->startOfYear()->toDateString(),
            default   => $this->from,
        };
        $this->until  = match ($period) {
            'month'   => now()->endOfMonth()->toDateString(),
            'quarter' => now()->endOfQuarter()->toDateString(),
            'year'    => now()->endOfYear()->toDateString(),
            default   => $this->until,
        };
    }

    // ── Core KPIs ─────────────────────────────────────────────────────────────

    public function getRevenue(): float
    {
        return (float) Payment::where('status', 'paid')
            ->whereBetween('payment_date', [$this->from, $this->until])
            ->sum('amount');
    }

    public function getExpensesTotal(): float
    {
        return (float) Expense::whereBetween('date', [$this->from, $this->until])->sum('amount');
    }

    public function getNetProfit(): float
    {
        return $this->getRevenue() - $this->getExpensesTotal();
    }

    public function getPendingTotal(): float
    {
        return (float) Payment::where('status', 'pending')->whereNotNull('due_date')->sum('amount');
    }

    public function getTotalOverdue(): float
    {
        return (float) Payment::where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->sum('amount');
    }

    public function getOverdueCount(): int
    {
        return Payment::where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->count();
    }

    public function getCollectionRate(): float
    {
        $totalDue = (float) Payment::sum('amount');
        if ($totalDue <= 0) return 0.0;
        $collected = (float) Payment::where('status', 'paid')->sum('amount');
        return round(min(100, ($collected / $totalDue) * 100), 1);
    }

    public function getPaymentPerformance(): float
    {
        $total = Payment::count();
        if ($total === 0) return 0.0;
        return round(Payment::where('status', 'paid')->count() / $total * 100, 1);
    }

    // ── Period comparison ─────────────────────────────────────────────────────

    private function getPreviousPeriodDates(): array
    {
        $from = Carbon::parse($this->from);
        $days = $from->diffInDays(Carbon::parse($this->until)) + 1;
        return [
            'from'  => $from->clone()->subDays($days)->toDateString(),
            'until' => $from->clone()->subDays(1)->toDateString(),
        ];
    }

    public function getRevenueGrowth(): float
    {
        $prev        = $this->getPreviousPeriodDates();
        $prevRevenue = (float) Payment::where('status', 'paid')
            ->whereBetween('payment_date', [$prev['from'], $prev['until']])
            ->sum('amount');
        if ($prevRevenue <= 0) return 0.0;
        return round(($this->getRevenue() - $prevRevenue) / $prevRevenue * 100, 1);
    }

    public function getExpenseGrowth(): float
    {
        $prev         = $this->getPreviousPeriodDates();
        $prevExpenses = (float) Expense::whereBetween('date', [$prev['from'], $prev['until']])->sum('amount');
        if ($prevExpenses <= 0) return 0.0;
        return round(($this->getExpensesTotal() - $prevExpenses) / $prevExpenses * 100, 1);
    }

    // ── Breakdowns ────────────────────────────────────────────────────────────

    public function getExpensesByCategory(): array
    {
        return Expense::selectRaw('expense_categories.name as category, SUM(expenses.amount) as total')
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->whereBetween('expenses.date', [$this->from, $this->until])
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->pluck('total', 'category')
            ->toArray();
    }

    public function getRevenueByPaymentMethod(): array
    {
        return Payment::selectRaw('payment_method, SUM(amount) as total')
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$this->from, $this->until])
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();
    }

    // ── Aging Analysis ────────────────────────────────────────────────────────

    public function getOverdueByAging(): array
    {
        $now = now();
        return [
            '1_30'  => (float) Payment::where('status', 'pending')
                ->whereDate('due_date', '>=', $now->clone()->subDays(30))
                ->whereDate('due_date', '<', $now)->sum('amount'),
            '31_60' => (float) Payment::where('status', 'pending')
                ->whereDate('due_date', '>=', $now->clone()->subDays(60))
                ->whereDate('due_date', '<', $now->clone()->subDays(30))->sum('amount'),
            '61_90' => (float) Payment::where('status', 'pending')
                ->whereDate('due_date', '>=', $now->clone()->subDays(90))
                ->whereDate('due_date', '<', $now->clone()->subDays(60))->sum('amount'),
            '90p'   => (float) Payment::where('status', 'pending')
                ->whereDate('due_date', '<', $now->clone()->subDays(90))->sum('amount'),
        ];
    }

    // ── Outstanding payments detailed table ───────────────────────────────────

    public function getOverduePaymentsDetailed(): array
    {
        return Payment::where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->with(['student' => fn ($q) => $q->with('classroom')])
            ->orderBy('due_date')
            ->take(20)
            ->get()
            ->map(fn ($p) => [
                'student_name' => $p->student?->full_name ?? '—',
                'classroom'    => $p->student?->classroom?->name ?? '—',
                'amount'       => (float) $p->amount,
                'due_date'     => $p->due_date ? Carbon::parse($p->due_date)->format('d/m/Y') : '—',
                'days_overdue' => $p->due_date ? (int) now()->diffInDays(Carbon::parse($p->due_date)) : 0,
            ])
            ->toArray();
    }

    // ── Chart data — Last 6 months ────────────────────────────────────────────

    public function getChartData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        return [
            'labels'   => $months->map(fn ($m) => ucfirst(
                Carbon::create($m->year, $m->month, 1)->locale(app()->getLocale())->isoFormat('MMM YY')
            ))->toArray(),
            'revenue'  => $months->map(fn ($m) => (float) Payment::where('status', 'paid')
                ->whereYear('payment_date', $m->year)
                ->whereMonth('payment_date', $m->month)
                ->sum('amount'))->toArray(),
            'expenses' => $months->map(fn ($m) => (float) Expense::whereYear('date', $m->year)
                ->whereMonth('date', $m->month)
                ->sum('amount'))->toArray(),
        ];
    }
}
