<x-filament-panels::page>
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Total annuel --}}
    <div style="border-radius:14px;padding:20px 24px;color:#fff;background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 24px rgba(16,185,129,.25);max-width:360px;">
        <div style="font-size:12.5px;opacity:.9;font-weight:600;">{{ __('Net perçu en :year', ['year' => $year]) }}</div>
        <div style="font-size:28px;font-weight:800;letter-spacing:-.5px;margin-top:4px;">{{ number_format($totalNet, 3, ',', ' ') }} TND</div>
    </div>

    @if($payslips->isEmpty())
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:48px;text-align:center;color:#94a3b8;">
            {{ __('Aucune fiche de paie disponible pour le moment.') }}
        </div>
    @else
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#fafbfc;">
                    <th style="text-align:left;padding:13px 22px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">{{ __('Période') }}</th>
                    <th style="text-align:right;padding:13px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">{{ __('Brut') }}</th>
                    <th style="text-align:right;padding:13px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">{{ __('CNSS') }}</th>
                    <th style="text-align:right;padding:13px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">{{ __('IRPP') }}</th>
                    <th style="text-align:right;padding:13px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">{{ __('Net') }}</th>
                    <th style="text-align:center;padding:13px 22px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">{{ __('Statut') }}</th>
                    <th style="text-align:right;padding:13px 22px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">PDF</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payslips as $p)
                <tr style="border-top:1px solid #f1f4f8;">
                    <td style="padding:14px 22px;font-size:13.5px;font-weight:600;color:#0f172a;">{{ $p['period'] }}</td>
                    <td style="padding:14px 14px;text-align:right;font-size:13px;color:#475569;font-variant-numeric:tabular-nums;">{{ number_format($p['gross'],3) }}</td>
                    <td style="padding:14px 14px;text-align:right;font-size:13px;color:#dc2626;font-variant-numeric:tabular-nums;">-{{ number_format($p['cnss'],3) }}</td>
                    <td style="padding:14px 14px;text-align:right;font-size:13px;color:#dc2626;font-variant-numeric:tabular-nums;">-{{ number_format($p['irpp'],3) }}</td>
                    <td style="padding:14px 14px;text-align:right;font-size:14px;font-weight:800;color:#0f172a;font-variant-numeric:tabular-nums;">{{ number_format($p['net'],3) }}</td>
                    <td style="padding:14px 22px;text-align:center;">
                        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:7px;
                            background:{{ $p['status']==='paid' ? '#ecfdf5' : ($p['status']==='finalized' ? '#fffbeb' : '#f1f5f9') }};
                            color:{{ $p['status']==='paid' ? '#059669' : ($p['status']==='finalized' ? '#b45309' : '#64748b') }};">
                            {{ ['paid'=>__('Payée'),'finalized'=>__('Finalisée'),'draft'=>__('Brouillon'),'rejected'=>__('Rejetée')][$p['status']] ?? $p['status'] }}
                        </span>
                    </td>
                    <td style="padding:14px 22px;text-align:right;">
                        <a href="{{ route('pdf.payslip', $p['id']) }}" target="_blank" style="color:#2563eb;font-size:12.5px;font-weight:700;text-decoration:none;">⬇ PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
</x-filament-panels::page>
