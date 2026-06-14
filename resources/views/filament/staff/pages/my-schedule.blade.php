<x-filament-panels::page>
@if($empty)
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:56px 24px;text-align:center;">
        <div style="font-size:40px;">🗓️</div>
        <div style="font-size:16px;font-weight:700;color:#1e293b;margin-top:12px;">Aucun cours planifié</div>
        <div style="font-size:13.5px;color:#94a3b8;margin-top:4px;">Votre emploi du temps apparaîtra ici une fois les séances assignées.</div>
    </div>
@else
<div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.05);">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#fafbfc;">
                <th style="padding:13px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;text-align:left;border-bottom:1px solid #eaeef3;">Jour</th>
                @foreach($slots as $s)
                <th style="padding:13px 14px;font-size:11px;font-weight:700;color:#64748b;text-align:center;border-bottom:1px solid #eaeef3;border-left:1px solid #f1f4f8;">
                    {{ $s['start'] }}<br><span style="font-weight:500;color:#94a3b8;">{{ $s['end'] }}</span>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($grid as $row)
            <tr>
                <td style="padding:12px 14px;font-size:13px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f4f8;background:#fafbfc;">{{ $row['day'] }}</td>
                @foreach($row['cells'] as $cell)
                <td style="padding:8px;border-bottom:1px solid #f1f4f8;border-left:1px solid #f1f4f8;vertical-align:top;">
                    @if($cell)
                    <div style="background:#eff6ff;border-left:3px solid #2563eb;border-radius:8px;padding:8px 10px;">
                        <div style="font-size:12.5px;font-weight:700;color:#1e3a8a;">{{ $cell->subject?->name ?? '—' }}</div>
                        <div style="font-size:11px;color:#3b82f6;margin-top:2px;">Classe {{ $cell->classroom?->name ?? '—' }}</div>
                        @if($cell->room)<div style="font-size:10.5px;color:#94a3b8;margin-top:1px;">{{ $cell->room }}</div>@endif
                    </div>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
</x-filament-panels::page>
