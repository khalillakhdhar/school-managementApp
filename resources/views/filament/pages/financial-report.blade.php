<x-filament-panels::page>

@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endassets

<style>
@media(max-width:1100px){
    .ec-kpi-grid{grid-template-columns:repeat(2,1fr)!important}
    .ec-charts-main{grid-template-columns:1fr!important}
    .ec-two-col{grid-template-columns:1fr!important}
    .ec-insights{grid-template-columns:repeat(2,1fr)!important}
    .ec-hdr-inner{flex-direction:column!important;gap:12px!important}
    .ec-period-row{flex-wrap:wrap!important}
}
@media(max-width:640px){
    .ec-kpi-grid{grid-template-columns:1fr 1fr!important}
    .ec-insights{grid-template-columns:1fr 1fr!important}
    .ec-hdr-inner{gap:10px!important}
}
@media print{
    .ec-cmd-bar,.ec-no-print{display:none!important}
}
</style>

@php
    $revenue       = $this->getRevenue();
    $expenses      = $this->getExpensesTotal();
    $net           = $this->getNetProfit();
    $overdue       = $this->getTotalOverdue();
    $overdueCount  = $this->getOverdueCount();
    $rate          = $this->getCollectionRate();
    $revenueGrowth = $this->getRevenueGrowth();
    $expenseGrowth = $this->getExpenseGrowth();
    $payPerf       = $this->getPaymentPerformance();
    $aging         = $this->getOverdueByAging();
    $overdueDetail = $this->getOverduePaymentsDetailed();
    $expByCat      = $this->getExpensesByCategory();
    $byMethod      = $this->getRevenueByPaymentMethod();
    $chartData     = $this->getChartData();

    $hasData = $revenue > 0 || $expenses > 0 || $overdue > 0;

    // SVG sparkline path generator
    $spark = function(array $values, int $w = 100, int $h = 34) {
        if (empty($values)) return "M0,{$h} L{$w},{$h}";
        $max = max($values); $min = min($values);
        if ($max <= 0) return 'M0,'.round($h/2).' L'.$w.','.round($h/2);
        $range = ($max - $min) ?: 1;
        $n = count($values);
        $pts = [];
        foreach ($values as $i => $v) {
            $x = round($i / max($n-1,1) * $w, 2);
            $y = round($h - (($v-$min)/$range)*($h*0.78) - $h*0.11, 2);
            $pts[] = "$x,$y";
        }
        return 'M'.implode(' L',$pts);
    };

    $netArr  = array_map(fn($r,$e)=>$r-$e, $chartData['revenue'], $chartData['expenses']);

    // Trend helpers
    $badge = function(float $pct, bool $invertBad = false) {
        $up = $pct > 0;
        if ($invertBad) { // for expenses: up = bad (red), down = good (green)
            return $up
                ? ['bg'=>'#fee2e2','clr'=>'#dc2626','arr'=>'↑']
                : ($pct < 0 ? ['bg'=>'#dcfce7','clr'=>'#16a34a','arr'=>'↓'] : ['bg'=>'#f1f5f9','clr'=>'#64748b','arr'=>'→']);
        }
        return $up
            ? ['bg'=>'#dcfce7','clr'=>'#16a34a','arr'=>'↑']
            : ($pct < 0 ? ['bg'=>'#fee2e2','clr'=>'#dc2626','arr'=>'↓'] : ['bg'=>'#f1f5f9','clr'=>'#64748b','arr'=>'→']);
    };

    $rBadge = $badge($revenueGrowth);
    $eBadge = $badge($expenseGrowth, true);
    $nBadge = $badge($net >= 0 ? 1 : -1);

    $fromFmt  = \Carbon\Carbon::parse($from)->locale(app()->getLocale())->isoFormat('D MMM YYYY');
    $untilFmt = \Carbon\Carbon::parse($until)->locale(app()->getLocale())->isoFormat('D MMM YYYY');
    $chartColors = ['#1d4ed8','#ef4444','#f59e0b','#10b981','#06b6d4','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'];
@endphp

<div style="display:flex;flex-direction:column;gap:20px;">

{{-- ═══════════════════════════════════════════════════════════════
     COMMAND BAR — gradient header with period selector + export
════════════════════════════════════════════════════════════════ --}}
<div class="ec-cmd-bar" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 55%,#1d4ed8 100%);border-radius:16px;padding:20px 24px;box-shadow:0 4px 24px rgba(15,23,42,0.28);">
    <div class="ec-hdr-inner" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">

        {{-- Title --}}
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;background:rgba(255,255,255,0.12);border-radius:12px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.15);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
            </div>
            <div>
                <div style="font-size:18px;font-weight:800;color:white;letter-spacing:-0.3px;">{{ __('Rapport Financier') }}</div>
                <div style="font-size:12px;color:rgba(147,197,253,0.85);margin-top:1px;">{{ $fromFmt }} → {{ $untilFmt }}</div>
            </div>
        </div>

        {{-- Controls --}}
        <div class="ec-period-row" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            {{-- Period toggles --}}
            @foreach(['month'=>__('Ce mois'),'quarter'=>__('Trimestre'),'year'=>__('Année')] as $key=>$lbl)
            <button wire:click="setPeriod('{{ $key }}')"
                style="padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid rgba(255,255,255,0.2);cursor:pointer;transition:all .15s;
                {{ $period===$key ? 'background:rgba(255,255,255,0.95);color:#1d4ed8;border-color:transparent;' : 'background:rgba(255,255,255,0.1);color:white;' }}">
                {{ $lbl }}
            </button>
            @endforeach

            {{-- Custom date range --}}
            <div style="display:flex;align-items:center;gap:4px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.18);border-radius:8px;padding:5px 10px;">
                <input type="date" wire:model.live="from"
                    style="background:transparent;color:white;font-size:12px;border:none;outline:none;color-scheme:dark;width:120px;">
                <span style="color:rgba(147,197,253,0.7);font-size:11px;font-weight:700;">→</span>
                <input type="date" wire:model.live="until"
                    style="background:transparent;color:white;font-size:12px;border:none;outline:none;color-scheme:dark;width:120px;">
            </div>

            {{-- Export CSV --}}
            <button class="ec-no-print" onclick="ecExportCsv()"
                style="display:flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:600;background:rgba(255,255,255,0.1);color:white;border:1px solid rgba(255,255,255,0.2);cursor:pointer;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                CSV
            </button>

            {{-- Print PDF --}}
            <button class="ec-no-print" onclick="window.print()"
                style="display:flex;align-items:center;gap:5px;padding:7px 12px;border-radius:8px;font-size:12px;font-weight:600;background:rgba(255,255,255,0.1);color:white;border:1px solid rgba(255,255,255,0.2);cursor:pointer;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                PDF
            </button>
        </div>
    </div>
</div>

@if(!$hasData)
{{-- ═══ EMPTY STATE ═══════════════════════════════════════════════════════ --}}
<div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:64px 24px;text-align:center;">
    <div style="width:72px;height:72px;background:#f0f9ff;border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
    </div>
    <div style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:8px;">{{ __('Aucune donnée financière disponible') }}</div>
    <div style="font-size:14px;color:#64748b;max-width:400px;margin:0 auto 24px;">{{ __('Commencez à enregistrer des paiements et des dépenses pour générer des rapports financiers détaillés.') }}</div>
    <a href="{{ \App\Filament\Resources\PaymentResource::getUrl('create') }}"
        style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#1d4ed8;color:white;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        {{ __('Enregistrer un paiement') }}
    </a>
</div>

@else

{{-- ═══════════════════════════════════════════════════════════════
     4 KPI CARDS
════════════════════════════════════════════════════════════════ --}}
<div class="ec-kpi-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">

    {{-- Card 1 — Revenus --}}
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:20px;overflow:hidden;position:relative;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
            <div style="width:40px;height:40px;background:#ecfdf5;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            @if($revenueGrowth != 0)
            <span style="padding:3px 8px;background:{{ $rBadge['bg'] }};color:{{ $rBadge['clr'] }};border-radius:20px;font-size:11px;font-weight:700;">{{ $rBadge['arr'] }} {{ abs($revenueGrowth) }}%</span>
            @endif
        </div>
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;">{{ __('Revenus') }}</div>
        <div style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.8px;line-height:1.1;margin:4px 0 2px;">{{ number_format($revenue, 3) }}</div>
        <div style="font-size:11px;color:#94a3b8;">{{ __('TND · cette période') }}</div>
        <svg viewBox="0 0 100 34" style="width:100%;height:34px;margin-top:12px;" preserveAspectRatio="none">
            <defs><linearGradient id="gRev" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity="0.18"/><stop offset="100%" stop-color="#10b981" stop-opacity="0"/></linearGradient></defs>
            @php $p = $spark($chartData['revenue']); @endphp
            <path d="{{ $p }} L100,34 L0,34 Z" fill="url(#gRev)"/>
            <path d="{{ $p }}" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    {{-- Card 2 — Dépenses --}}
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:20px;overflow:hidden;position:relative;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
            <div style="width:40px;height:40px;background:#fff1f2;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
            </div>
            @if($expenseGrowth != 0)
            <span style="padding:3px 8px;background:{{ $eBadge['bg'] }};color:{{ $eBadge['clr'] }};border-radius:20px;font-size:11px;font-weight:700;">{{ $eBadge['arr'] }} {{ abs($expenseGrowth) }}%</span>
            @endif
        </div>
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;">{{ __('Dépenses') }}</div>
        <div style="font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.8px;line-height:1.1;margin:4px 0 2px;">{{ number_format($expenses, 3) }}</div>
        <div style="font-size:11px;color:#94a3b8;">{{ __('TND · cette période') }}</div>
        <svg viewBox="0 0 100 34" style="width:100%;height:34px;margin-top:12px;" preserveAspectRatio="none">
            <defs><linearGradient id="gExp" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#ef4444" stop-opacity="0.18"/><stop offset="100%" stop-color="#ef4444" stop-opacity="0"/></linearGradient></defs>
            @php $p = $spark($chartData['expenses']); @endphp
            <path d="{{ $p }} L100,34 L0,34 Z" fill="url(#gExp)"/>
            <path d="{{ $p }}" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    {{-- Card 3 — Impayés --}}
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:20px;overflow:hidden;position:relative;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
            <div style="width:40px;height:40px;background:#fffbeb;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            @if($overdueCount > 0)
            <span style="padding:3px 8px;background:#fee2e2;color:#dc2626;border-radius:20px;font-size:11px;font-weight:700;">{{ __(':count en retard', ['count' => $overdueCount]) }}</span>
            @endif
        </div>
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;">{{ __('Impayés') }}</div>
        <div style="font-size:26px;font-weight:800;color:#d97706;letter-spacing:-0.8px;line-height:1.1;margin:4px 0 2px;">{{ number_format($overdue, 3) }}</div>
        <div style="font-size:11px;color:#94a3b8;">{{ __('TND · total en retard') }}</div>
        <svg viewBox="0 0 100 34" style="width:100%;height:34px;margin-top:12px;" preserveAspectRatio="none">
            <defs><linearGradient id="gOvd" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#f59e0b" stop-opacity="0.18"/><stop offset="100%" stop-color="#f59e0b" stop-opacity="0"/></linearGradient></defs>
            @php $p = $spark(array_values($aging)); @endphp
            <path d="{{ $p }} L100,34 L0,34 Z" fill="url(#gOvd)"/>
            <path d="{{ $p }}" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    {{-- Card 4 — Résultat Net --}}
    @php $netColor = $net >= 0 ? '#1d4ed8' : '#ef4444'; $netBg = $net >= 0 ? '#eff6ff' : '#fff1f2'; @endphp
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:20px;overflow:hidden;position:relative;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
            <div style="width:40px;height:40px;background:{{ $netBg }};border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="{{ $netColor }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="{{ $net >= 0 ? '23 6 13.5 15.5 8.5 10.5 1 18' : '1 6 10.5 15.5 15.5 10.5 23 18' }}"/></svg>
            </div>
            <span style="padding:3px 8px;background:{{ $nBadge['bg'] }};color:{{ $nBadge['clr'] }};border-radius:20px;font-size:11px;font-weight:700;">{{ $net >= 0 ? __('Bénéfice') : __('Déficit') }}</span>
        </div>
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;">{{ __('Résultat Net') }}</div>
        <div style="font-size:26px;font-weight:800;color:{{ $netColor }};letter-spacing:-0.8px;line-height:1.1;margin:4px 0 2px;">{{ $net >= 0 ? '+' : '' }}{{ number_format($net, 3) }}</div>
        <div style="font-size:11px;color:#94a3b8;">{{ __('TND · recettes − dépenses') }}</div>
        <svg viewBox="0 0 100 34" style="width:100%;height:34px;margin-top:12px;" preserveAspectRatio="none">
            <defs><linearGradient id="gNet" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="{{ $netColor }}" stop-opacity="0.18"/><stop offset="100%" stop-color="{{ $netColor }}" stop-opacity="0"/></linearGradient></defs>
            @php $p = $spark($netArr); @endphp
            <path d="{{ $p }} L100,34 L0,34 Z" fill="url(#gNet)"/>
            <path d="{{ $p }}" fill="none" stroke="{{ $netColor }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     CHARTS — Revenue Evolution (line) + Expense Distribution (donut)
════════════════════════════════════════════════════════════════ --}}
<div class="ec-charts-main" style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">

    {{-- Revenue Evolution Line Chart --}}
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:22px 24px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;">
            <div>
                <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ __('Évolution des revenus') }}</div>
                <div style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ __('Tendance sur les 6 derniers mois') }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:14px;font-size:12px;color:#64748b;">
                <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:3px;background:#10b981;border-radius:2px;display:inline-block;"></span>{{ __('Revenus') }}</span>
                <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:3px;background:#ef4444;border-radius:2px;display:inline-block;"></span>{{ __('Dépenses') }}</span>
            </div>
        </div>
        <div style="position:relative;height:220px;">
            <canvas id="ecRevLine"></canvas>
        </div>
    </div>

    {{-- Expense Distribution Donut --}}
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:22px 24px;">
        <div style="margin-bottom:16px;">
            <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ __('Répartition des dépenses') }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ __('Par catégorie · période en cours') }}</div>
        </div>
        @if(empty($expByCat))
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:200px;color:#94a3b8;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:10px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            <span style="font-size:13px;">{{ __('Aucune dépense sur cette période') }}</span>
        </div>
        @else
        <div style="position:relative;height:170px;">
            <canvas id="ecExpDonut"></canvas>
        </div>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:5px;">
            @foreach(array_slice($expByCat,0,5,true) as $cat=>$amt)
            @php $ci=$loop->index; $pct=$expenses>0?round($amt/$expenses*100):0; @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;">
                <span style="display:flex;align-items:center;gap:5px;color:#475569;overflow:hidden;max-width:60%;">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $chartColors[$ci%count($chartColors)] }};flex-shrink:0;display:inline-block;"></span>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $cat }}</span>
                </span>
                <span style="color:#0f172a;font-weight:600;white-space:nowrap;">{{ $pct }}%</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     CASH FLOW — Bar chart with trend summary
════════════════════════════════════════════════════════════════ --}}
<div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:22px 24px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ __('Flux de trésorerie') }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:2px;">{{ __('Revenus, dépenses et résultat net par mois') }}</div>
        </div>
        {{-- Summary row --}}
        <div style="display:flex;align-items:center;gap:20px;">
            @php $totalRev=$expenses6m=0; foreach($chartData['revenue'] as $v){$totalRev+=$v;} foreach($chartData['expenses'] as $v){$expenses6m+=$v;} $netFlow=$totalRev-$expenses6m; @endphp
            <div style="text-align:end;">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#64748b;">{{ __('Entrées 6 mois') }}</div>
                <div style="font-size:16px;font-weight:800;color:#10b981;">{{ number_format($totalRev,0,'.',' ') }} TND</div>
            </div>
            <div style="width:1px;height:36px;background:#e2e8f0;"></div>
            <div style="text-align:end;">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#64748b;">{{ __('Sorties 6 mois') }}</div>
                <div style="font-size:16px;font-weight:800;color:#ef4444;">{{ number_format($expenses6m,0,'.',' ') }} TND</div>
            </div>
            <div style="width:1px;height:36px;background:#e2e8f0;"></div>
            <div style="text-align:end;">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#64748b;">{{ __('Flux net') }}</div>
                <div style="font-size:16px;font-weight:800;color:{{ $netFlow>=0?'#1d4ed8':'#ef4444' }};">{{ $netFlow>=0?'+':'' }}{{ number_format($netFlow,0,'.',' ') }} TND</div>
            </div>
        </div>
    </div>
    <div style="position:relative;height:200px;">
        <canvas id="ecCashFlow"></canvas>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     BREAKDOWN — Payment methods (left) + Aging analysis (right)
════════════════════════════════════════════════════════════════ --}}
<div class="ec-two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

    {{-- Payment Methods --}}
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:22px 24px;">
        <div style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:4px;">{{ __('Revenus par mode de paiement') }}</div>
        <div style="font-size:12px;color:#94a3b8;margin-bottom:18px;">{{ __('Répartition des encaissements') }}</div>
        @if(empty($byMethod))
        <div style="color:#94a3b8;font-size:13px;padding:20px 0;">{{ __('Aucun revenu sur cette période') }}</div>
        @else
        @php
            $methodMeta = [
                'cash'          => ['icon'=>'💵','label'=>__('Espèces'),         'color'=>'#10b981'],
                'bank_transfer' => ['icon'=>'🏦','label'=>__('Virement bancaire'),'color'=>'#1d4ed8'],
                'cheque'        => ['icon'=>'📋','label'=>__('Chèque'),          'color'=>'#f59e0b'],
                'app'           => ['icon'=>'📱','label'=>__('Application'),     'color'=>'#8b5cf6'],
            ];
        @endphp
        <div style="display:flex;flex-direction:column;gap:14px;">
            @foreach($byMethod as $method=>$amount)
            @php $meta=$methodMeta[$method]??['icon'=>'💰','label'=>$method,'color'=>'#64748b']; $pct=$revenue>0?round($amount/$revenue*100):0; @endphp
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <span style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#374151;">
                        <span>{{ $meta['icon'] }}</span>{{ $meta['label'] }}
                    </span>
                    <span style="font-size:12px;color:#64748b;font-variant-numeric:tabular-nums;">{{ number_format($amount,0,'.',' ') }} TND &nbsp;<span style="color:#94a3b8;">({{ $pct }}%)</span></span>
                </div>
                <div style="width:100%;background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden;">
                    <div style="width:{{ $pct }}%;background:{{ $meta['color'] }};height:6px;border-radius:4px;transition:width .6s;"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Aging Analysis --}}
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:22px 24px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
            <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ __('Analyse par ancienneté des impayés') }}</div>
            @if(array_sum($aging)>0)
            <span style="padding:2px 8px;background:#fee2e2;color:#dc2626;border-radius:12px;font-size:11px;font-weight:700;">{{ number_format(array_sum($aging),0,'.',' ') }} TND</span>
            @endif
        </div>
        <div style="font-size:12px;color:#94a3b8;margin-bottom:18px;">{{ __('Répartition des retards de paiement') }}</div>
        @php
            $agingBuckets = [
                ['label'=>__('1–30 jours'),   'key'=>'1_30',  'color'=>'#f59e0b','bg'=>'#fffbeb','text'=>'#92400e'],
                ['label'=>__('31–60 jours'),  'key'=>'31_60', 'color'=>'#f97316','bg'=>'#fff7ed','text'=>'#9a3412'],
                ['label'=>__('61–90 jours'),  'key'=>'61_90', 'color'=>'#ef4444','bg'=>'#fff1f2','text'=>'#b91c1c'],
                ['label'=>__('90+ jours'),    'key'=>'90p',   'color'=>'#dc2626','bg'=>'#fef2f2','text'=>'#991b1b'],
            ];
            $agingTotal = array_sum($aging) ?: 1;
        @endphp
        <div style="display:flex;flex-direction:column;gap:10px;">
            @foreach($agingBuckets as $b)
            @php $pct=$aging[$b['key']]>0?round($aging[$b['key']]/$agingTotal*100):0; @endphp
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:8px;height:8px;border-radius:50%;background:{{ $b['color'] }};flex-shrink:0;"></div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                        <span style="font-weight:600;color:#374151;">{{ $b['label'] }}</span>
                        <span style="color:{{ $aging[$b['key']]>0?$b['text']:'#94a3b8' }};font-weight:{{ $aging[$b['key']]>0?'700':'400' }};font-variant-numeric:tabular-nums;">
                            {{ $aging[$b['key']]>0 ? number_format($aging[$b['key']],0,'.',' ').' TND' : '—' }}
                        </span>
                    </div>
                    <div style="width:100%;background:#f1f5f9;border-radius:4px;height:5px;overflow:hidden;">
                        <div style="width:{{ $pct }}%;background:{{ $b['color'] }};height:5px;border-radius:4px;transition:width .6s;"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     OUTSTANDING PAYMENTS — Professional table
════════════════════════════════════════════════════════════════ --}}
@if(!empty($overdueDetail))
<div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);overflow:hidden;">
    {{-- Table header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px 16px;border-bottom:1px solid #f1f5f9;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;background:#fff1f2;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ __('Paiements en retard') }}</div>
                <div style="font-size:12px;color:#94a3b8;">{{ __(':count élève(s) · total :total TND', ['count' => count($overdueDetail), 'total' => number_format($overdue,0,'.',' ')]) }}</div>
            </div>
        </div>
        <a href="{{ \App\Filament\Resources\PaymentResource::getUrl('index') }}"
            style="display:flex;align-items:center;gap:5px;padding:7px 14px;background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;font-size:12px;font-weight:600;color:#dc2626;text-decoration:none;">
            {{ __('Voir tous') }}
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
        </a>
    </div>
    {{-- Table --}}
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:10px 24px 10px 24px;text-align:start;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;white-space:nowrap;">#</th>
                    <th style="padding:10px 16px;text-align:start;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;">{{ __('Élève') }}</th>
                    <th style="padding:10px 16px;text-align:start;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;">{{ __('Classe') }}</th>
                    <th style="padding:10px 16px;text-align:end;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;">{{ __('Montant dû') }}</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;">{{ __('Échéance') }}</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;">{{ __('Retard') }}</th>
                    <th style="padding:10px 24px 10px 16px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#94a3b8;">{{ __('Statut') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($overdueDetail as $idx => $row)
                @php
                    $days = $row['days_overdue'];
                    if ($days > 60)      { $sLabel=__('Critique'); $sBg='#fef2f2'; $sClr='#dc2626'; $sBorder='#fecaca'; }
                    elseif ($days > 30)  { $sLabel=__('Urgent');   $sBg='#fff7ed'; $sClr='#c2410c'; $sBorder='#fed7aa'; }
                    else                 { $sLabel=__('En retard'); $sBg='#fffbeb'; $sClr='#d97706'; $sBorder='#fde68a'; }
                @endphp
                <tr style="border-top:1px solid #f8fafc;transition:background .1s;" onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:12px 24px;font-size:12px;color:#94a3b8;font-variant-numeric:tabular-nums;">{{ $idx+1 }}</td>
                    <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#0f172a;">{{ $row['student_name'] }}</td>
                    <td style="padding:12px 16px;font-size:12px;color:#64748b;">
                        @if($row['classroom'] !== '—')
                        <span style="padding:2px 8px;background:#eff6ff;color:#1d4ed8;border-radius:6px;font-size:11px;font-weight:600;">{{ $row['classroom'] }}</span>
                        @else —
                        @endif
                    </td>
                    <td style="padding:12px 16px;text-align:end;font-size:13px;font-weight:800;color:#dc2626;font-variant-numeric:tabular-nums;">{{ number_format($row['amount'],3) }} <span style="font-size:10px;font-weight:500;color:#94a3b8;">TND</span></td>
                    <td style="padding:12px 16px;text-align:center;font-size:12px;color:#475569;font-variant-numeric:tabular-nums;">{{ $row['due_date'] }}</td>
                    <td style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:{{ $sClr }};font-variant-numeric:tabular-nums;">{{ __(':n j', ['n' => $days]) }}</td>
                    <td style="padding:12px 24px 12px 16px;text-align:center;">
                        <span style="display:inline-flex;align-items:center;padding:3px 10px;background:{{ $sBg }};border:1px solid {{ $sBorder }};border-radius:20px;font-size:10px;font-weight:700;color:{{ $sClr }};">{{ $sLabel }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     FINANCIAL INSIGHTS — 4 metric cards
════════════════════════════════════════════════════════════════ --}}
<div class="ec-insights" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">

    {{-- Collection Rate --}}
    @php $rateColor = $rate>=80?'#10b981':($rate>=50?'#f59e0b':'#ef4444'); @endphp
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;">{{ __('Collection Rate') }}</div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $rateColor }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div style="font-size:30px;font-weight:900;color:{{ $rateColor }};letter-spacing:-1px;line-height:1;">{{ $rate }}<span style="font-size:18px;">%</span></div>
        <div style="margin-top:12px;width:100%;background:#f1f5f9;border-radius:4px;height:6px;">
            <div style="width:{{ $rate }}%;background:{{ $rateColor }};height:6px;border-radius:4px;transition:width .8s;"></div>
        </div>
        <div style="font-size:11px;color:#94a3b8;margin-top:6px;">{{ __('du total dû est encaissé') }}</div>
    </div>

    {{-- Revenue Growth --}}
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;">{{ __('Croissance revenus') }}</div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        @if($revenueGrowth != 0)
        <div style="font-size:30px;font-weight:900;color:{{ $rBadge['clr'] }};letter-spacing:-1px;line-height:1;">{{ $rBadge['arr'] }} {{ abs($revenueGrowth) }}<span style="font-size:18px;">%</span></div>
        @else
        <div style="font-size:30px;font-weight:900;color:#64748b;letter-spacing:-1px;line-height:1;">→ 0%</div>
        @endif
        <div style="font-size:11px;color:#94a3b8;margin-top:8px;">{{ __('vs période précédente') }}</div>
        <div style="margin-top:10px;padding:8px 12px;background:{{ $rBadge['bg'] }};border-radius:8px;">
            <div style="font-size:12px;font-weight:600;color:{{ $rBadge['clr'] }};">{{ __(':amount TND cette période', ['amount' => number_format($revenue,0,'.',' ')]) }}</div>
        </div>
    </div>

    {{-- Expense Growth --}}
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;">{{ __('Croissance dépenses') }}</div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
        </div>
        @if($expenseGrowth != 0)
        <div style="font-size:30px;font-weight:900;color:{{ $eBadge['clr'] }};letter-spacing:-1px;line-height:1;">{{ $eBadge['arr'] }} {{ abs($expenseGrowth) }}<span style="font-size:18px;">%</span></div>
        @else
        <div style="font-size:30px;font-weight:900;color:#64748b;letter-spacing:-1px;line-height:1;">→ 0%</div>
        @endif
        <div style="font-size:11px;color:#94a3b8;margin-top:8px;">{{ __('vs période précédente') }}</div>
        <div style="margin-top:10px;padding:8px 12px;background:{{ $eBadge['bg'] }};border-radius:8px;">
            <div style="font-size:12px;font-weight:600;color:{{ $eBadge['clr'] }};">{{ __(':amount TND cette période', ['amount' => number_format($expenses,0,'.',' ')]) }}</div>
        </div>
    </div>

    {{-- Payment Performance --}}
    @php $perfColor = $payPerf>=80?'#1d4ed8':($payPerf>=50?'#0ea5e9':'#94a3b8'); @endphp
    <div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;">{{ __('Performance paiements') }}</div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $perfColor }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </div>
        <div style="font-size:30px;font-weight:900;color:{{ $perfColor }};letter-spacing:-1px;line-height:1;">{{ $payPerf }}<span style="font-size:18px;">%</span></div>
        <div style="margin-top:12px;width:100%;background:#f1f5f9;border-radius:4px;height:6px;">
            <div style="width:{{ $payPerf }}%;background:{{ $perfColor }};height:6px;border-radius:4px;transition:width .8s;"></div>
        </div>
        <div style="font-size:11px;color:#94a3b8;margin-top:6px;">{{ __('des paiements sont soldés') }}</div>
    </div>
</div>

@endif {{-- end hasData --}}
</div>

{{-- ═══════════════════════════════════════════════════════════════
     CHART.JS INITIALIZATION
════════════════════════════════════════════════════════════════ --}}
@script
<script>
(function() {
    const labels    = @json($chartData['labels']);
    const revenue   = @json($chartData['revenue']);
    const expenses  = @json($chartData['expenses']);
    const netFlow   = revenue.map((r,i) => r - expenses[i]);
    const expCat    = @json($expByCat);
    const colors    = @json($chartColors);

    const defaultFont = { family: 'Inter, sans-serif' };
    const tooltip = {
        backgroundColor: 'rgba(15,23,42,0.92)',
        titleFont: { ...defaultFont, size: 12, weight: '700' },
        bodyFont:  { ...defaultFont, size: 11 },
        padding: 10,
        cornerRadius: 8,
        callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('fr-TN', {minimumFractionDigits:3,maximumFractionDigits:3})} TND` }
    };

    // ── Revenue Evolution Line Chart ─────────────────────────────────────────
    Chart.getChart('ecRevLine')?.destroy();
    const revEl = document.getElementById('ecRevLine');
    if (revEl) {
        new Chart(revEl, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: '{{ __('Revenus') }}',
                        data: revenue,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.08)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.4,
                    },
                    {
                        label: '{{ __('Dépenses') }}',
                        data: expenses,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239,68,68,0.06)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.4,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip },
                scales: {
                    x: { grid: { display: false }, ticks: { font: defaultFont, size: 11, color: '#9ca3af' } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(156,163,175,0.1)' },
                        ticks: {
                            font: defaultFont,
                            color: '#9ca3af',
                            callback: v => v >= 1000 ? (v/1000).toFixed(1)+'k' : v.toFixed(0)
                        }
                    }
                }
            }
        });
    }

    // ── Expense Distribution Donut ───────────────────────────────────────────
    Chart.getChart('ecExpDonut')?.destroy();
    const donutEl = document.getElementById('ecExpDonut');
    if (donutEl && Object.keys(expCat).length > 0) {
        new Chart(donutEl, {
            type: 'doughnut',
            data: {
                labels: Object.keys(expCat),
                datasets: [{
                    data: Object.values(expCat),
                    backgroundColor: colors,
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltip,
                        callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('fr-TN', {minimumFractionDigits:3,maximumFractionDigits:3})} TND` }
                    }
                }
            }
        });
    }

    // ── Cash Flow Bar Chart ──────────────────────────────────────────────────
    Chart.getChart('ecCashFlow')?.destroy();
    const cfEl = document.getElementById('ecCashFlow');
    if (cfEl) {
        new Chart(cfEl, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: '{{ __('Revenus') }}',
                        data: revenue,
                        backgroundColor: 'rgba(16,185,129,0.7)',
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: '{{ __('Dépenses') }}',
                        data: expenses,
                        backgroundColor: 'rgba(239,68,68,0.65)',
                        borderColor: '#ef4444',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: '{{ __('Flux net') }}',
                        data: netFlow,
                        type: 'line',
                        borderColor: '#1d4ed8',
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        pointRadius: 5,
                        pointBackgroundColor: '#1d4ed8',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                        tension: 0.35,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, boxWidth: 8, font: defaultFont, padding: 16, color: '#64748b' }
                    },
                    tooltip: { ...tooltip, callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('fr-TN', {minimumFractionDigits:3,maximumFractionDigits:3})} TND` } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: defaultFont, color: '#9ca3af' } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(156,163,175,0.1)' },
                        ticks: {
                            font: defaultFont, color: '#9ca3af',
                            callback: v => v >= 1000 ? (v/1000).toFixed(1)+'k' : v.toFixed(0)
                        }
                    }
                }
            }
        });
    }

    // ── CSV Export function ──────────────────────────────────────────────────
    window.ecExportCsv = function() {
        const rows = @json($overdueDetail);
        if (!rows || rows.length === 0) {
            alert('{{ __('Aucun impayé à exporter.') }}');
            return;
        }
        let csv = '﻿'; // UTF-8 BOM
        csv += '{{ __('Élève') }},{{ __('Classe') }},{{ __('Montant dû') }} (TND),{{ __("Date d'échéance") }},{{ __('Jours de retard') }}\n';
        rows.forEach(r => {
            csv += `"${r.student_name}","${r.classroom}","${parseFloat(r.amount).toFixed(3)}","${r.due_date}","${r.days_overdue}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url;
        a.download = 'rapport-financier-{{ now()->format("Y-m-d") }}.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };
})();
</script>
@endscript

</x-filament-panels::page>
