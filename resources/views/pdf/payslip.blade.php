<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
@php
    $months = [1=>'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    $period = ($months[$p->month] ?? $p->month) . ' ' . $p->year;
    $statusLabels = ['draft'=>'Brouillon','finalized'=>'Finalisée','paid'=>'Payée','rejected'=>'Rejetée'];
@endphp
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { color: #1e293b; font-size: 12px; margin: 0; }
    .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 4px; }
    .header td { vertical-align: top; }
    .school { font-size: 18px; font-weight: bold; color: #0f172a; }
    .doc { font-size: 12px; color: #64748b; }
    .emp { font-size: 14px; font-weight: bold; color: #0f172a; text-align: right; }
    .emp-sub { font-size: 11px; color: #64748b; text-align: right; }
    h3 { font-size: 11px; text-transform: uppercase; color: #64748b; margin: 18px 0 6px; }
    table.lines { width: 100%; border-collapse: collapse; }
    table.lines td { padding: 6px 10px; border-bottom: 1px solid #eef2f7; }
    table.lines td.right { text-align: right; }
    table.lines tr.sub td { font-weight: bold; background: #f8fafc; border-top: 1px solid #cbd5e1; }
    .net { background: #2563eb; color: #fff; padding: 14px; border-radius: 8px; margin-top: 16px; }
    .net td { color: #fff; }
    .net .label { font-size: 12px; }
    .net .val { font-size: 22px; font-weight: bold; text-align: right; }
    .muted { color: #94a3b8; }
    .footer { margin-top: 30px; font-size: 10px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
</style>
</head>
<body>
    <table class="header" width="100%">
        <tr>
            <td>
                <div class="school">{{ $schoolName }}</div>
                <div class="doc">Bulletin de paie — {{ $period }}</div>
            </td>
            <td>
                <div class="emp">{{ $p->employee?->full_name ?? '—' }}</div>
                <div class="emp-sub">{{ $p->employee?->position ?? ($p->employee?->is_teacher ? 'Enseignant(e)' : '') }}</div>
                @if($p->employee?->matricule_cnss)<div class="emp-sub">CNSS : {{ $p->employee->matricule_cnss }}</div>@endif
            </td>
        </tr>
    </table>

    <h3>Rémunération brute</h3>
    <table class="lines">
        <tr><td>Salaire de base</td><td class="right">{{ number_format((float)$p->salary_base, 3) }} TND</td></tr>
        @if((float)$p->overtime_pay > 0)<tr><td>Heures supplémentaires</td><td class="right">{{ number_format((float)$p->overtime_pay, 3) }} TND</td></tr>@endif
        @if((float)$p->bonuses > 0)<tr><td>Primes</td><td class="right">{{ number_format((float)$p->bonuses, 3) }} TND</td></tr>@endif
        @if((float)$p->indemnite_transport > 0)<tr><td>Indemnité de transport</td><td class="right">{{ number_format((float)$p->indemnite_transport, 3) }} TND</td></tr>@endif
        @if((float)$p->indemnite_logement > 0)<tr><td>Indemnité de logement</td><td class="right">{{ number_format((float)$p->indemnite_logement, 3) }} TND</td></tr>@endif
        <tr class="sub"><td>Salaire brut</td><td class="right">{{ number_format((float)$p->gross_salary, 3) }} TND</td></tr>
    </table>

    <h3>Retenues salariales</h3>
    <table class="lines">
        <tr><td>CNSS (9,18%)</td><td class="right">- {{ number_format((float)$p->cnss_deduction, 3) }} TND</td></tr>
        <tr><td>IRPP</td><td class="right">- {{ number_format((float)$p->irpp_deduction, 3) }} TND</td></tr>
        @if((float)$p->retenue_source > 0)<tr><td>Retenue à la source</td><td class="right">- {{ number_format((float)$p->retenue_source, 3) }} TND</td></tr>@endif
        @if((float)$p->other_deductions > 0)<tr><td>Autres retenues</td><td class="right">- {{ number_format((float)$p->other_deductions, 3) }} TND</td></tr>@endif
    </table>

    <table class="net" width="100%">
        <tr>
            <td class="label">NET À PAYER</td>
            <td class="val">{{ number_format((float)$p->net_salary, 3) }} TND</td>
        </tr>
    </table>

    <h3>Charges patronales (information)</h3>
    <table class="lines">
        <tr><td class="muted">CNSS patronale (16,57%)</td><td class="right muted">{{ number_format((float)$p->cnss_patronale, 3) }} TND</td></tr>
        <tr><td class="muted">FOPROLOS (1%)</td><td class="right muted">{{ number_format((float)$p->foprolos, 3) }} TND</td></tr>
        <tr><td class="muted">Total charges patronales</td><td class="right muted">{{ number_format((float)$p->total_charge_patronale, 3) }} TND</td></tr>
    </table>

    <div class="footer">Statut : {{ $statusLabels[$p->status] ?? $p->status }} · {{ $schoolName }} — Généré le {{ now()->format('d/m/Y') }}</div>
</body>
</html>
