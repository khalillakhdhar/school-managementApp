<?php
namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Payment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class FinancialReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    public function getView(): string { return 'filament.pages.financial-report'; }
    protected static ?int    $navigationSort = 1;

    public string $period  = 'month';
    public ?string $from   = null;
    public ?string $until  = null;

    public static function getNavigationLabel(): string  { return __('Financial Report'); }
    public static function getNavigationGroup(): ?string { return __('Finance'); }
    public function getTitle(): string                   { return __('Financial Report'); }

    public function mount(): void
    {
        $this->from  = now()->startOfMonth()->toDateString();
        $this->until = now()->endOfMonth()->toDateString();
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;

        $this->from = match ($period) {
            'month'   => now()->startOfMonth()->toDateString(),
            'quarter' => now()->startOfQuarter()->toDateString(),
            'year'    => now()->startOfYear()->toDateString(),
            default   => $this->from,
        };

        $this->until = match ($period) {
            'month'   => now()->endOfMonth()->toDateString(),
            'quarter' => now()->endOfQuarter()->toDateString(),
            'year'    => now()->endOfYear()->toDateString(),
            default   => $this->until,
        };
    }

    public function getRevenue(): float
    {
        return Payment::where('status', 'paid')
            ->whereBetween('payment_date', [$this->from, $this->until])
            ->sum('amount');
    }

    public function getExpensesTotal(): float
    {
        return Expense::whereBetween('date', [$this->from, $this->until])->sum('amount');
    }

    public function getNetProfit(): float
    {
        return $this->getRevenue() - $this->getExpensesTotal();
    }

    public function getRevenueByMonth(): array
    {
        return Payment::selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount) as total')
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$this->from, $this->until])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
    }

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

    public function getPendingTotal(): float
    {
        return Payment::where('status', 'pending')
            ->whereBetween('payment_date', [$this->from, $this->until])
            ->sum('amount');
    }
}
