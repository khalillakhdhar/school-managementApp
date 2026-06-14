<x-filament-panels::page>
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Pointage du jour --}}
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(16,24,40,.05);display:flex;flex-wrap:wrap;align-items:center;gap:20px;">
        <div style="flex:1;min-width:180px;">
            <div style="font-size:13px;color:#64748b;font-weight:600;">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</div>
            <div style="display:flex;gap:24px;margin-top:8px;">
                <div><div style="font-size:11px;color:#94a3b8;font-weight:600;">ARRIVÉE</div><div style="font-size:22px;font-weight:800;color:{{ $today && $today['in'] ? '#059669':'#cbd5e1' }};">{{ $today['in'] ?? '--:--' }}</div></div>
                <div><div style="font-size:11px;color:#94a3b8;font-weight:600;">DÉPART</div><div style="font-size:22px;font-weight:800;color:{{ $today && $today['out'] ? '#dc2626':'#cbd5e1' }};">{{ $today['out'] ?? '--:--' }}</div></div>
            </div>
        </div>
        <div style="display:flex;gap:10px;">
            <button wire:click="clockIn" type="button" @if($today && $today['in']) disabled @endif
                style="background:{{ $today && $today['in'] ? '#f1f5f9':'#10b981' }};color:{{ $today && $today['in'] ? '#94a3b8':'#fff' }};border:none;border-radius:10px;padding:12px 22px;font-size:14px;font-weight:700;cursor:{{ $today && $today['in'] ? 'default':'pointer' }};">
                Pointer l'arrivée
            </button>
            <button wire:click="clockOut" type="button" @if($today && $today['out']) disabled @endif
                style="background:{{ $today && $today['out'] ? '#f1f5f9':'#2563eb' }};color:{{ $today && $today['out'] ? '#94a3b8':'#fff' }};border:none;border-radius:10px;padding:12px 22px;font-size:14px;font-weight:700;cursor:{{ $today && $today['out'] ? 'default':'pointer' }};">
                Pointer le départ
            </button>
        </div>
    </div>

    {{-- KPIs du mois --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
        @foreach([['Jours pointés',$stats['days'],'#2563eb'],['Présences',$stats['present'],'#10b981'],['Absences',$stats['absent'],'#ef4444']] as $kpi)
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:12px;padding:16px 18px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
            <div style="font-size:24px;font-weight:800;color:{{ $kpi[2] }};">{{ $kpi[1] }}</div>
            <div style="font-size:12px;color:#64748b;font-weight:600;margin-top:3px;">{{ $kpi[0] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Historique du mois --}}
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        <div style="padding:14px 22px;border-bottom:1px solid #f1f4f8;font-size:14px;font-weight:700;color:#0f172a;">Historique — {{ now()->locale('fr')->isoFormat('MMMM YYYY') }}</div>
        @if($rows->isEmpty())
            <div style="padding:36px;text-align:center;color:#94a3b8;font-size:13px;">Aucun pointage ce mois.</div>
        @else
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#fafbfc;">
                    <th style="text-align:left;padding:11px 22px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Date</th>
                    <th style="text-align:center;padding:11px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Statut</th>
                    <th style="text-align:center;padding:11px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Arrivée</th>
                    <th style="text-align:center;padding:11px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Départ</th>
                    <th style="text-align:right;padding:11px 22px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Heures</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                <tr style="border-top:1px solid #f1f4f8;">
                    <td style="padding:12px 22px;font-size:13px;font-weight:600;color:#0f172a;">{{ $r['date'] }}</td>
                    <td style="padding:12px 14px;text-align:center;">
                        <span style="font-size:11px;font-weight:700;padding:2px 9px;border-radius:7px;
                            background:{{ ['present'=>'#ecfdf5','late'=>'#fffbeb','absent'=>'#fef2f2','leave'=>'#eef2ff'][$r['status']] ?? '#f1f5f9' }};
                            color:{{ ['present'=>'#059669','late'=>'#b45309','absent'=>'#dc2626','leave'=>'#4f46e5'][$r['status']] ?? '#64748b' }};">
                            {{ ['present'=>'Présent','late'=>'Retard','absent'=>'Absent','leave'=>'Congé'][$r['status']] ?? $r['status'] }}
                        </span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;font-size:13px;color:#475569;">{{ $r['in'] }}</td>
                    <td style="padding:12px 14px;text-align:center;font-size:13px;color:#475569;">{{ $r['out'] }}</td>
                    <td style="padding:12px 22px;text-align:right;font-size:13px;font-weight:700;color:#0f172a;">{{ $r['hours'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>
</x-filament-panels::page>
