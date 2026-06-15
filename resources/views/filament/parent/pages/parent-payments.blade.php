<x-filament-panels::page>
@if(empty($children))
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:24px;color:#92400e;">
        <strong>Aucun enfant rattaché à votre compte.</strong>
    </div>
@else
<div style="display:flex;flex-direction:column;gap:18px;">

    {{-- Solde global --}}
    <div style="border-radius:14px;padding:20px 24px;color:#fff;max-width:360px;
        background:{{ $totalOutstanding > 0 ? 'linear-gradient(135deg,#f59e0b,#d97706)' : 'linear-gradient(135deg,#10b981,#059669)' }};
        box-shadow:0 8px 24px rgba(0,0,0,.12);">
        <div style="font-size:12.5px;opacity:.9;font-weight:600;">Solde total dû</div>
        <div style="font-size:28px;font-weight:800;letter-spacing:-.5px;margin-top:4px;">{{ number_format($totalOutstanding, 3, ',', ' ') }} TND</div>
    </div>

    @foreach($children as $child)
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        <div style="padding:16px 22px;border-bottom:1px solid #f1f4f8;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
            <div>
                <div style="font-size:16px;font-weight:800;color:#0f172a;">{{ $child['name'] }}</div>
                <div style="font-size:12.5px;color:#64748b;">Classe {{ $child['class'] }}</div>
            </div>
            <div style="display:flex;gap:20px;">
                <div style="text-align:right;"><div style="font-size:11px;color:#94a3b8;font-weight:600;">PAYÉ</div><div style="font-size:15px;font-weight:800;color:#059669;">{{ number_format($child['paid'],3) }}</div></div>
                <div style="text-align:right;"><div style="font-size:11px;color:#94a3b8;font-weight:600;">EN ATTENTE</div><div style="font-size:15px;font-weight:800;color:#b45309;">{{ number_format($child['pending'],3) }}</div></div>
                <div style="text-align:right;"><div style="font-size:11px;color:#94a3b8;font-weight:600;">EN RETARD</div><div style="font-size:15px;font-weight:800;color:{{ $child['overdue']>0 ? '#dc2626':'#94a3b8' }};">{{ number_format($child['overdue'],3) }}</div></div>
            </div>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#fafbfc;">
                    <th style="text-align:left;padding:11px 22px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Échéance</th>
                    <th style="text-align:left;padding:11px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Date limite</th>
                    <th style="text-align:right;padding:11px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Montant</th>
                    <th style="text-align:center;padding:11px 22px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($child['payments'] as $p)
                <tr style="border-top:1px solid #f1f4f8;">
                    <td style="padding:12px 22px;font-size:13px;font-weight:600;color:#0f172a;">{{ $p['label'] }}</td>
                    <td style="padding:12px 14px;font-size:12.5px;color:#64748b;">{{ $p['due'] }}</td>
                    <td style="padding:12px 14px;text-align:right;font-size:13px;font-weight:700;color:#0f172a;font-variant-numeric:tabular-nums;">{{ number_format($p['amount'],3) }}</td>
                    <td style="padding:12px 22px;text-align:center;">
                        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:7px;
                            background:{{ ['paid'=>'#ecfdf5','pending'=>'#fffbeb','overdue'=>'#fef2f2'][$p['status']] }};
                            color:{{ ['paid'=>'#059669','pending'=>'#b45309','overdue'=>'#dc2626'][$p['status']] }};">
                            {{ ['paid'=>'Payé','pending'=>'En attente','overdue'=>'En retard'][$p['status']] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">Aucun paiement enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endforeach

</div>
@endif
</x-filament-panels::page>
