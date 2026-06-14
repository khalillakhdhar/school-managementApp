@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endassets

<x-filament-widgets::widget>
@php
    $chartColors = ['#1d4ed8','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#f97316'];
    $maxEvo = max(max($evolution['counts'] ?: [1]), 1);
@endphp

<div style="display:flex;flex-direction:column;gap:20px;">

{{-- ══════════════════════════════════════════════════════════════
     1. KPI CARDS ROW
══════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;">

    {{-- Students --}}
    <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Élèves actifs</span>
            <div style="width:34px;height:34px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
        </div>
        <div style="font-size:30px;font-weight:800;color:#0f172a;letter-spacing:-1px;line-height:1;">{{ $activeStudents }}</div>
        <div style="font-size:11px;font-weight:600;color:{{ $studentTrend >= 0 ? '#10b981' : '#ef4444' }};">
            {{ $studentTrend >= 0 ? '+' : '' }}{{ $studentTrend }}% · {{ $newThisMonth }} ce mois
        </div>
        <div style="display:flex;align-items:flex-end;gap:2px;height:24px;margin-top:2px;">
            @foreach($evolution['counts'] as $count)
            @php $h = max(3, round($count/$maxEvo*24)); @endphp
            <div style="flex:1;height:{{ $h }}px;background:#bfdbfe;border-radius:2px 2px 0 0;"></div>
            @endforeach
        </div>
    </div>

    {{-- Teachers --}}
    <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Enseignants</span>
            <div style="width:34px;height:34px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                    <path d="M16 3v4M8 3v4M3 11h18"/>
                </svg>
            </div>
        </div>
        <div style="font-size:30px;font-weight:800;color:#0f172a;letter-spacing:-1px;line-height:1;">{{ $teachersCount }}</div>
        <div style="font-size:11px;color:#64748b;">actifs dans l'établissement</div>
        <div style="height:24px;background:#d1fae5;border-radius:4px;margin-top:2px;"></div>
    </div>

    {{-- Classes --}}
    <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Classes</span>
            <div style="width:34px;height:34px;background:#faf5ff;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
        </div>
        <div style="font-size:30px;font-weight:800;color:#0f172a;letter-spacing:-1px;line-height:1;">{{ $classesCount }}</div>
        <div style="font-size:11px;color:#64748b;">{{ $activeStudents > 0 && $classesCount > 0 ? round($activeStudents/$classesCount) : 0 }} élèves/classe en moy.</div>
        <div style="height:24px;background:#ede9fe;border-radius:4px;margin-top:2px;"></div>
    </div>

    {{-- Attendance --}}
    <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Taux présence</span>
            <div style="width:34px;height:34px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>
        <div style="font-size:30px;font-weight:800;color:#0f172a;letter-spacing:-1px;line-height:1;">{{ $attendanceRate }}<span style="font-size:16px;color:#64748b;font-weight:600;">%</span></div>
        <div style="font-size:11px;color:#64748b;">{{ $totalAtt }} enregistrements ce mois</div>
        <div style="background:#fef3c7;border-radius:4px;height:24px;overflow:hidden;position:relative;margin-top:2px;">
            <div style="position:absolute;left:0;top:0;bottom:0;width:{{ $attendanceRate }}%;background:#f59e0b;border-radius:4px;transition:width .6s;"></div>
        </div>
    </div>

    {{-- Revenue --}}
    <div style="background:white;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Recettes mois</span>
            <div style="width:34px;height:34px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
        </div>
        <div style="font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;line-height:1.1;">{{ number_format($revenueMonth,0,',',' ') }} TND</div>
        <div style="font-size:11px;font-weight:600;color:{{ $revenueTrend >= 0 ? '#10b981' : '#ef4444' }};">
            {{ $revenueTrend >= 0 ? '↑' : '↓' }} {{ abs($revenueTrend) }}% vs mois précédent
        </div>
        <div style="display:flex;align-items:flex-end;gap:2px;height:24px;margin-top:2px;">
            @php $maxRev = max(max($revenueChart['revenue'] ?: [1]),1); @endphp
            @foreach($revenueChart['revenue'] as $r)
            @php $h = max(3, round($r/$maxRev*24)); @endphp
            <div style="flex:1;height:{{ $h }}px;background:#bbf7d0;border-radius:2px 2px 0 0;"></div>
            @endforeach
        </div>
    </div>

    {{-- Overdue --}}
    <div style="background:{{ $overdueCount > 0 ? '#fff1f2' : 'white' }};border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px {{ $overdueCount > 0 ? 'rgba(239,68,68,0.15)' : 'rgba(0,0,0,0.04)' }};display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Impayés</span>
            <div style="width:34px;height:34px;background:#fff1f2;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $overdueCount > 0 ? '#ef4444' : '#94a3b8' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
        </div>
        <div style="font-size:22px;font-weight:800;color:{{ $overdueCount > 0 ? '#dc2626' : '#0f172a' }};letter-spacing:-0.5px;line-height:1.1;">{{ number_format($overdueTotal,0,',',' ') }} TND</div>
        <div style="font-size:11px;font-weight:600;color:{{ $overdueCount > 0 ? '#dc2626' : '#10b981' }};">
            {{ $overdueCount > 0 ? $overdueCount.' paiement(s) échu(s)' : 'Aucun impayé' }}
        </div>
        <div style="height:24px;background:{{ $overdueCount > 0 ? '#fecaca' : '#d1fae5' }};border-radius:4px;margin-top:2px;"></div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     2. CHARTS ROW
══════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">

    {{-- Student Evolution Line Chart --}}
    <div style="background:white;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">Évolution des élèves</h3>
                <p style="font-size:12px;color:#64748b;margin:3px 0 0;">6 derniers mois</p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;font-size:11px;color:#64748b;">
                <span style="display:flex;align-items:center;gap:4px;">
                    <span style="width:12px;height:3px;background:#1d4ed8;border-radius:2px;display:inline-block;"></span>Élèves
                </span>
            </div>
        </div>
        <div style="position:relative;height:200px;">
            <canvas id="evolutionChart"></canvas>
        </div>
    </div>

    {{-- Class Distribution Doughnut --}}
    <div style="background:white;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);">
        <div style="margin-bottom:16px;">
            <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">Répartition par niveau</h3>
            <p style="font-size:12px;color:#64748b;margin:3px 0 0;">Élèves actifs</p>
        </div>
        @if(empty($distribution))
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:180px;color:#94a3b8;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <span style="font-size:13px;margin-top:8px;">Aucune donnée</span>
            </div>
        @else
            <div style="position:relative;height:160px;">
                <canvas id="distributionChart"></canvas>
            </div>
            <div style="display:flex;flex-direction:column;gap:5px;margin-top:12px;">
                @foreach($distribution as $i => $d)
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $chartColors[$i % count($chartColors)] }};flex-shrink:0;"></span>
                        <span style="color:#475569;">{{ $d['label'] }}</span>
                    </div>
                    <span style="font-weight:700;color:#0f172a;">{{ $d['count'] }}</span>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     3. BOTTOM ROW: Overdue Table + Activities + Financial
══════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;">

    {{-- Overdue Payments Table --}}
    <div style="background:white;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">Paiements en retard</h3>
                <p style="font-size:12px;color:#64748b;margin:3px 0 0;">{{ $overdueCount }} paiement(s) échu(s)</p>
            </div>
            @if($overdueCount > 0)
            <a href="{{ \App\Filament\Resources\PaymentResource::getUrl('index') }}"
               style="font-size:12px;font-weight:600;color:#1d4ed8;text-decoration:none;display:flex;align-items:center;gap:3px;">
                Voir tout
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
            </a>
            @endif
        </div>

        @if(empty($overdueTable))
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:140px;gap:10px;">
                <div style="width:44px;height:44px;background:#f0fdf4;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <span style="font-size:13px;font-weight:600;color:#10b981;">Aucun paiement en retard</span>
                <span style="font-size:12px;color:#94a3b8;">Tous les paiements sont à jour</span>
            </div>
        @else
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <th style="text-align:left;font-size:10px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;padding:0 0 10px;">Élève</th>
                        <th style="text-align:left;font-size:10px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;padding:0 0 10px;">Classe</th>
                        <th style="text-align:right;font-size:10px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;padding:0 0 10px;">Montant</th>
                        <th style="text-align:right;font-size:10px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;padding:0 0 10px;">Retard</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueTable as $row)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:9px 0;font-size:13px;font-weight:600;color:#0f172a;">{{ $row['student'] }}</td>
                        <td style="padding:9px 0;font-size:12px;color:#64748b;">
                            <span style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-weight:500;">{{ $row['class'] }}</span>
                        </td>
                        <td style="padding:9px 0;font-size:13px;font-weight:700;color:#0f172a;text-align:right;font-variant-numeric:tabular-nums;">{{ number_format($row['amount'],3) }}</td>
                        <td style="padding:9px 0;text-align:right;">
                            <span style="font-size:11px;font-weight:700;color:white;padding:2px 8px;border-radius:20px;
                                background:{{ $row['days'] > 60 ? '#dc2626' : ($row['days'] > 30 ? '#f59e0b' : '#ef4444') }};">
                                {{ $row['days'] }}j
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Right column --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Financial Summary --}}
        <div style="background:white;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);">
            <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 14px;">Résumé financier</h3>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:#64748b;">Total facturé ({{ now()->year }})</span>
                    <span style="font-size:13px;font-weight:700;color:#0f172a;font-variant-numeric:tabular-nums;">{{ number_format($invoiced,3) }} TND</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:#64748b;">Total encaissé</span>
                    <span style="font-size:13px;font-weight:700;color:#10b981;font-variant-numeric:tabular-nums;">{{ number_format($received,3) }} TND</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:#64748b;">En attente</span>
                    <span style="font-size:13px;font-weight:700;color:#f59e0b;font-variant-numeric:tabular-nums;">{{ number_format($pending,3) }} TND</span>
                </div>
                <div style="border-top:1px solid #f1f5f9;padding-top:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-size:13px;font-weight:600;color:#0f172a;">Taux de recouvrement</span>
                        <span style="font-size:16px;font-weight:800;color:{{ $collectRate >= 80 ? '#10b981' : ($collectRate >= 50 ? '#f59e0b' : '#ef4444') }};">{{ $collectRate }}%</span>
                    </div>
                    <div style="background:#f1f5f9;border-radius:4px;height:8px;overflow:hidden;">
                        <div style="height:100%;width:{{ $collectRate }}%;background:{{ $collectRate >= 80 ? '#10b981' : ($collectRate >= 50 ? '#f59e0b' : '#ef4444') }};border-radius:4px;transition:width .8s;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div style="background:white;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.06),0 0 0 1px rgba(0,0,0,0.04);flex:1;">
            <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 14px;">Activités récentes</h3>
            @if(empty($activities))
                <div style="display:flex;align-items:center;gap:8px;color:#94a3b8;font-size:13px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                    Aucune activité récente
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:0;">
                    @foreach($activities as $i => $act)
                    <div style="display:flex;align-items:flex-start;gap:10px;padding:9px 0;{{ !$loop->last ? 'border-bottom:1px solid #f8fafc;' : '' }}">
                        <div style="width:28px;height:28px;border-radius:50%;background:{{ $act['color'] }}20;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            @if($act['type'] === 'payment')
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="{{ $act['color'] }}" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                            @elseif($act['type'] === 'incident')
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="{{ $act['color'] }}" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            @else
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="{{ $act['color'] }}" stroke-width="2.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            @endif
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12px;font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $act['text'] }}</div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:1px;">{{ $act['meta'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

</div>{{-- end main container --}}

@script
<script>
// Revenue / Student Evolution Line Chart
const evoEl = document.getElementById('evolutionChart');
if (evoEl) {
    Chart.getChart('evolutionChart')?.destroy();
    new Chart(evoEl, {
        type: 'line',
        data: {
            labels: @json($evolution['labels']),
            datasets: [{
                label: 'Élèves',
                data: @json($evolution['counts']),
                borderColor: '#1d4ed8',
                backgroundColor: 'rgba(29,78,216,0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#1d4ed8',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.9)',
                    padding: 10,
                    cornerRadius: 8,
                    titleFont: { size: 12, weight: '700' },
                    bodyFont: { size: 12 },
                    callbacks: { label: ctx => ' ' + ctx.parsed.y + ' élèves' }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                y: {
                    beginAtZero: false,
                    grid: { color: 'rgba(241,245,249,1)', drawBorder: false },
                    ticks: { font: { size: 11 }, color: '#94a3b8' }
                }
            }
        }
    });
}

// Distribution Doughnut
const distEl = document.getElementById('distributionChart');
if (distEl) {
    Chart.getChart('distributionChart')?.destroy();
    const distData = @json($distribution);
    if (distData.length > 0) {
        new Chart(distEl, {
            type: 'doughnut',
            data: {
                labels: distData.map(d => d.label),
                datasets: [{
                    data: distData.map(d => d.count),
                    backgroundColor: @json($chartColors),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.9)',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' élèves'
                        }
                    }
                }
            }
        });
    }
}
</script>
@endscript
</x-filament-widgets::widget>
