<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { color: #1e293b; font-size: 12px; margin: 0; }
    .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 16px; }
    .header td { vertical-align: top; }
    .school { font-size: 18px; font-weight: bold; color: #0f172a; }
    .doc { font-size: 12px; color: #64748b; }
    .student { font-size: 14px; font-weight: bold; color: #0f172a; text-align: right; }
    .student-sub { font-size: 11px; color: #64748b; text-align: right; }
    table.grades { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    table.grades th { background: #f1f5f9; color: #334155; font-size: 10px; text-transform: uppercase;
        padding: 8px 10px; border-bottom: 1px solid #cbd5e1; text-align: left; }
    table.grades th.num, table.grades td.num { text-align: center; }
    table.grades th.right, table.grades td.right { text-align: right; }
    table.grades td { padding: 8px 10px; border-bottom: 1px solid #eef2f7; }
    table.grades tr.total td { background: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1; }
    .pass { color: #059669; font-weight: bold; }
    .fail { color: #dc2626; font-weight: bold; }
    .summary { width: 100%; border-collapse: separate; border-spacing: 8px 0; }
    .summary td { width: 33%; vertical-align: top; }
    .box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
    .box-primary { background: #2563eb; color: #fff; border: none; }
    .box-label { font-size: 9px; text-transform: uppercase; color: #64748b; }
    .box-primary .box-label { color: #dbeafe; }
    .box-value { font-size: 22px; font-weight: bold; margin-top: 4px; }
    .footer { margin-top: 30px; font-size: 10px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    .signs { width: 100%; margin-top: 40px; }
    .signs td { width: 50%; font-size: 11px; color: #475569; }
</style>
</head>
<body>
    <table class="header" width="100%">
        <tr>
            <td>
                <div class="school">{{ $schoolName }}</div>
                <div class="doc">Bulletin de notes — {{ $report['termLabel'] }} · {{ \App\Services\DemoDataService::ACADEMIC_YEAR ?? now()->year }}</div>
            </td>
            <td>
                <div class="student">{{ $report['student']->full_name }}</div>
                <div class="student-sub">Classe {{ $report['student']->classroom?->name ?? '—' }}</div>
            </td>
        </tr>
    </table>

    @if(! $report['hasGrades'])
        <p style="text-align:center;color:#94a3b8;padding:30px 0;">Aucune note saisie pour ce trimestre.</p>
    @else
    <table class="grades">
        <thead>
            <tr>
                <th>Matière</th>
                <th class="num">Note /20</th>
                <th class="num">Coef.</th>
                <th class="right">Points</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['lines'] as $line)
            <tr>
                <td>{{ $line['subject'] }}</td>
                <td class="num"><span class="{{ $line['note'] >= 10 ? 'pass' : 'fail' }}">{{ number_format($line['note'], 2) }}</span></td>
                <td class="num">{{ rtrim(rtrim(number_format($line['coef'], 2), '0'), '.') }}</td>
                <td class="right">{{ number_format($line['points'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td>Total</td>
                <td class="num"></td>
                <td class="num">{{ rtrim(rtrim(number_format($report['totalCoef'], 2), '0'), '.') }}</td>
                <td class="right">{{ number_format($report['totalPoints'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>
                <div class="box box-primary">
                    <div class="box-label">Moyenne générale</div>
                    <div class="box-value">{{ $report['average'] !== null ? number_format($report['average'], 2) : '—' }}/20</div>
                </div>
            </td>
            <td>
                <div class="box">
                    <div class="box-label">Rang</div>
                    <div class="box-value" style="color:#0f172a;">{{ $report['rank'] ?? '—' }}{{ $report['classSize'] ? ' / '.$report['classSize'] : '' }}</div>
                </div>
            </td>
            <td>
                <div class="box">
                    <div class="box-label">Mention</div>
                    <div class="box-value" style="font-size:15px;color:#1e40af;">{{ $report['mention'] ?? '—' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="signs">
        <tr>
            <td>Signature de l'enseignant(e)</td>
            <td style="text-align:right;">Signature de la direction</td>
        </tr>
    </table>
    @endif

    <div class="footer">{{ $schoolName }} — Document généré le {{ now()->format('d/m/Y') }}</div>
</body>
</html>
