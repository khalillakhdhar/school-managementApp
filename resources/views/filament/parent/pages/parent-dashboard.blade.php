<x-filament-panels::page>
@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endassets

@if(! $parent)
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:24px;color:#92400e;">
        <strong>{{ __('Aucun profil parent associé à ce compte.') }}</strong>
        <div style="margin-top:6px;font-size:13px;">{{ __("Contactez l'administration de l'établissement.") }}</div>
    </div>
@else
<div style="display:flex;flex-direction:column;gap:18px;">

    {{-- Hero --}}
    <div style="border-radius:16px;padding:24px 28px;color:#fff;background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 8px 24px rgba(37,99,235,.3);">
        <div style="font-size:13px;opacity:.85;font-weight:600;">{{ __('Portail Parents') }}</div>
        <div style="font-size:24px;font-weight:800;letter-spacing:-.4px;margin-top:2px;">{{ __('Bonjour, :name', ['name' => $parent->first_name]) }} 👋</div>
        <div style="font-size:13.5px;opacity:.9;margin-top:4px;">{{ __(':count enfant(s)', ['count' => $childrenCount]) }} · {{ now()->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY') }}</div>
    </div>

    {{-- KPI cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
        @php
        $kpis = [
            [__('Solde dû'), number_format($totalOutstanding,3).' TND', $totalOutstanding>0?'#f59e0b':'#10b981', $totalOutstanding>0?'#fffbeb':'#ecfdf5', $totalOutstanding>0?__('À régler'):__('Tout est payé')],
            [__('Taux de présence'), $avgAttendance.'%', $avgAttendance>=90?'#10b981':($avgAttendance>=75?'#f59e0b':'#ef4444'), '#eff6ff', __('Ce mois-ci')],
            [__('Incidents'), $incidentsMonth, $incidentsMonth>0?'#ef4444':'#10b981', $incidentsMonth>0?'#fef2f2':'#ecfdf5', __('Ce mois-ci')],
            [__('Prochaine échéance'), $nextDue ? number_format($nextDue['amount'],3).' TND' : '—', '#2563eb', '#eff6ff', $nextDue ? __('Avant le :date', ['date' => $nextDue['date']]) : __('Aucune échéance')],
        ];
        @endphp
        @foreach($kpis as $k)
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
            <div style="font-size:12.5px;color:#64748b;font-weight:600;">{{ $k[0] }}</div>
            <div style="font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-.5px;margin-top:6px;">{{ $k[1] }}</div>
            <div style="display:inline-block;font-size:11px;font-weight:600;color:{{ $k[2] }};background:{{ $k[3] }};padding:2px 9px;border-radius:6px;margin-top:8px;">{{ $k[4] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Charts row --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
            <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 4px;">Évolution de la présence</h3>
            <p style="font-size:12px;color:#64748b;margin:0 0 14px;">6 derniers mois</p>
            <div style="position:relative;height:200px;"><canvas id="parentAttendanceChart"></canvas></div>
        </div>
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
            <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 14px;">Répartition des paiements</h3>
            <div style="position:relative;height:150px;"><canvas id="parentPaymentChart"></canvas></div>
            <div style="display:flex;flex-direction:column;gap:6px;margin-top:14px;">
                @foreach([['Payé',$paymentBreakdown['paid'],'#10b981'],['En attente',$paymentBreakdown['pending'],'#f59e0b'],['En retard',$paymentBreakdown['overdue'],'#ef4444']] as $pb)
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;">
                    <span style="display:flex;align-items:center;gap:6px;"><span style="width:9px;height:9px;border-radius:50%;background:{{ $pb[2] }};"></span><span style="color:#475569;">{{ $pb[0] }}</span></span>
                    <span style="font-weight:700;color:#0f172a;">{{ number_format($pb[1],3) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Children + activity --}}
    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;">
        {{-- Children --}}
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
            <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 14px;">Mes enfants</h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($children as $c)
                <div style="display:flex;align-items:center;gap:14px;padding:12px 14px;background:#f8fafc;border-radius:11px;">
                    <div style="width:40px;height:40px;border-radius:11px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;flex-shrink:0;">
                        {{ strtoupper(mb_substr($c['name'],0,1)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ $c['name'] }}</div>
                        <div style="font-size:12px;color:#64748b;">Classe {{ $c['class'] }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:15px;font-weight:800;color:{{ $c['rate']===null ? '#94a3b8' : ($c['rate']>=90?'#059669':($c['rate']>=75?'#b45309':'#dc2626')) }};">{{ $c['rate']===null ? '—' : $c['rate'].'%' }}</div>
                        <div style="font-size:10.5px;color:#94a3b8;font-weight:600;">PRÉSENCE</div>
                    </div>
                    @if($c['outstanding']>0)
                    <div style="text-align:right;">
                        <div style="font-size:14px;font-weight:800;color:#b45309;">{{ number_format($c['outstanding'],3) }}</div>
                        <div style="font-size:10.5px;color:#94a3b8;font-weight:600;">DÛ</div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Activity + announcements --}}
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 12px;">Activité récente</h3>
                @forelse($activities as $a)
                <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid #f8fafc;' : '' }}">
                    <div style="width:28px;height:28px;border-radius:50%;background:{{ $a['color'] }}20;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">{{ $a['icon'] }}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12.5px;font-weight:600;color:#1e293b;">{{ $a['text'] }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $a['meta'] }} · {{ $a['ago'] }}</div>
                    </div>
                </div>
                @empty
                <div style="color:#94a3b8;font-size:13px;padding:8px 0;">Aucune activité récente.</div>
                @endforelse
            </div>

            <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(16,24,40,.05);flex:1;">
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 12px;">Dernières annonces</h3>
                @forelse($announcements as $ann)
                <div style="display:flex;gap:9px;align-items:flex-start;padding:7px 0;{{ !$loop->last ? 'border-bottom:1px solid #f8fafc;' : '' }}">
                    <span style="font-size:14px;">📣</span>
                    <div><div style="font-size:12.5px;font-weight:600;color:#1e293b;">{{ $ann['title'] }}</div><div style="font-size:11px;color:#94a3b8;">{{ $ann['date'] }}</div></div>
                </div>
                @empty
                <div style="color:#94a3b8;font-size:13px;">Aucune annonce.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@script
<script>
const attEl = document.getElementById('parentAttendanceChart');
if (attEl) {
    Chart.getChart('parentAttendanceChart')?.destroy();
    new Chart(attEl, {
        type: 'line',
        data: {
            labels: @json($attendanceTrend['labels']),
            datasets: [{
                label: 'Présence %', data: @json($attendanceTrend['rates']),
                borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.08)',
                borderWidth: 2.5, fill: true, tension: 0.4, pointBackgroundColor: '#2563eb', pointRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + c.parsed.y + '%' } } },
            scales: { x: { grid: { display: false }, ticks:{ color:'#94a3b8', font:{size:11} } },
                      y: { min: 0, max: 100, grid: { color: '#f1f5f9' }, ticks:{ color:'#94a3b8', font:{size:11}, callback: v => v + '%' } } }
        }
    });
}
const payEl = document.getElementById('parentPaymentChart');
if (payEl) {
    Chart.getChart('parentPaymentChart')?.destroy();
    const d = @json(array_values($paymentBreakdown));
    if (d.reduce((a,b)=>a+b,0) > 0) {
        new Chart(payEl, {
            type: 'doughnut',
            data: { labels: ['Payé','En attente','En retard'], datasets: [{ data: d, backgroundColor: ['#10b981','#f59e0b','#ef4444'], borderWidth: 2, borderColor: '#fff', hoverOffset: 5 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false } } }
        });
    }
}
</script>
@endscript
@endif
</x-filament-panels::page>
