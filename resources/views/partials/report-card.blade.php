{{-- Bulletin imprimable — attend $report (ReportCardService::forStudent) et $schoolName --}}
<style>
@media print {
    body * { visibility: hidden; }
    #report-card, #report-card * { visibility: visible; }
    #report-card { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
}
</style>
<div id="report-card" style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:32px 36px;box-shadow:0 1px 3px rgba(16,24,40,.05);max-width:780px;">
    {{-- En-tête --}}
    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid #0f172a;padding-bottom:16px;margin-bottom:20px;">
        <div>
            <div style="font-size:18px;font-weight:800;color:#0f172a;">{{ $schoolName ?? 'EliteCampus' }}</div>
            <div style="font-size:12px;color:#64748b;">{{ __('Bulletin de notes — :term', ['term' => $report['termLabel']]) }}</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:15px;font-weight:700;color:#0f172a;">{{ $report['student']->full_name }}</div>
            <div style="font-size:12px;color:#64748b;">{{ __('Classe :class', ['class' => $report['student']->classroom?->name ?? '—']) }}</div>
        </div>
    </div>

    @if(! $report['hasGrades'])
        <div style="padding:30px;text-align:center;color:#94a3b8;font-size:14px;">{{ __('Aucune note saisie pour ce trimestre.') }}</div>
    @else
    {{-- Tableau des matières --}}
    <table style="width:100%;border-collapse:collapse;margin-bottom:22px;">
        <thead>
            <tr style="background:#fafbfc;">
                <th style="text-align:left;padding:10px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:1px solid #eaeef3;">{{ __('Matière') }}</th>
                <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:1px solid #eaeef3;">{{ __('Note /20') }}</th>
                <th style="text-align:center;padding:10px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:1px solid #eaeef3;">{{ __('Coeff.') }}</th>
                <th style="text-align:right;padding:10px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:1px solid #eaeef3;">{{ __('Points') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['lines'] as $line)
            <tr style="border-bottom:1px solid #f1f4f8;">
                <td style="padding:11px 14px;font-size:13.5px;font-weight:600;color:#0f172a;">{{ $line['subject'] }}</td>
                <td style="padding:11px 14px;text-align:center;font-size:14px;font-weight:700;color:{{ $line['note']>=10 ? '#059669':'#dc2626' }};">{{ number_format($line['note'],2) }}</td>
                <td style="padding:11px 14px;text-align:center;font-size:13px;color:#64748b;">{{ rtrim(rtrim(number_format($line['coef'],2),'0'),'.') }}</td>
                <td style="padding:11px 14px;text-align:right;font-size:13px;font-weight:600;color:#475569;">{{ number_format($line['points'],2) }}</td>
            </tr>
            @endforeach
            <tr style="background:#f8fafc;border-top:2px solid #e5e9f0;">
                <td style="padding:11px 14px;font-size:13px;font-weight:700;color:#0f172a;">{{ __('Total') }}</td>
                <td></td>
                <td style="padding:11px 14px;text-align:center;font-size:13px;font-weight:700;color:#0f172a;">{{ rtrim(rtrim(number_format($report['totalCoef'],2),'0'),'.') }}</td>
                <td style="padding:11px 14px;text-align:right;font-size:13px;font-weight:700;color:#0f172a;">{{ number_format($report['totalPoints'],2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Synthèse --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
        <div style="background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:12px;padding:16px 18px;color:#fff;">
            <div style="font-size:11px;opacity:.85;font-weight:600;text-transform:uppercase;">{{ __('Moyenne générale') }}</div>
            <div style="font-size:30px;font-weight:800;margin-top:4px;">{{ $report['average']!==null ? number_format($report['average'],2) : '—' }}<span style="font-size:15px;opacity:.8;">/20</span></div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e5e9f0;border-radius:12px;padding:16px 18px;">
            <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;">{{ __('Rang') }}</div>
            <div style="font-size:26px;font-weight:800;color:#0f172a;margin-top:4px;">{{ $report['rank'] ?? '—' }}<span style="font-size:14px;color:#94a3b8;">{{ $report['classSize'] ? ' / '.$report['classSize'] : '' }}</span></div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e5e9f0;border-radius:12px;padding:16px 18px;">
            <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;">{{ __('Mention') }}</div>
            <div style="font-size:18px;font-weight:800;margin-top:8px;color:{{ in_array($report['mention'],['Félicitations','Très bien']) ? '#059669' : ($report['mention']==='Insuffisant' ? '#dc2626':'#b45309') }};">{{ $report['mention'] ? __($report['mention']) : '—' }}</div>
        </div>
    </div>
    @endif
</div>
