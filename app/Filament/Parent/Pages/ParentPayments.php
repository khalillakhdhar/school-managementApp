<?php
namespace App\Filament\Parent\Pages;

use App\Models\Payment;
use Filament\Pages\Page;

class ParentPayments extends Page
{
    protected string $view = 'filament.parent.pages.parent-payments';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string { return __('Paiements'); }
    public function getTitle(): string { return __('Paiements & soldes'); }

    protected function getViewData(): array
    {
        $parent = auth()->user()?->parent;
        if (! $parent) {
            return ['children' => [], 'totalOutstanding' => 0];
        }

        $now = now();
        $totalOutstanding = 0;
        $children = $parent->students()->with('classroom')->get()->map(function ($student) use ($now, &$totalOutstanding) {
            $payments = Payment::where('student_id', $student->id)->orderByDesc('due_date')->get();

            $paid     = (float) $payments->where('status', 'paid')->sum('amount');
            $pending  = (float) $payments->where('status', 'pending')->sum('amount');
            $overdue  = (float) $payments->where('status', 'pending')
                ->filter(fn ($p) => $p->due_date && $p->due_date->lt($now))->sum('amount');
            $totalOutstanding += $pending;

            return [
                'name'      => $student->full_name,
                'class'     => $student->classroom?->name ?? '—',
                'paid'      => $paid,
                'pending'   => $pending,
                'overdue'   => $overdue,
                'payments'  => $payments->take(12)->map(fn ($p) => [
                    'label'   => $p->notes ?: ($p->due_date?->locale(app()->getLocale())->isoFormat('MMMM YYYY') ?? '—'),
                    'amount'  => (float) $p->amount,
                    'due'     => $p->due_date?->format('d/m/Y') ?? '—',
                    'status'  => $p->status === 'paid'
                        ? 'paid'
                        : (($p->due_date && $p->due_date->lt($now)) ? 'overdue' : 'pending'),
                ])->toArray(),
            ];
        })->toArray();

        return ['children' => $children, 'totalOutstanding' => $totalOutstanding];
    }
}
