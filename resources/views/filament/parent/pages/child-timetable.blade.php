<x-filament-panels::page>
@if(empty($children))
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:24px;color:#92400e;">
        <strong>{{ __('Aucun enfant rattaché à votre compte.') }}</strong>
    </div>
@else
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Child selector --}}
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:14px 20px;box-shadow:0 1px 3px rgba(16,24,40,.05);display:flex;align-items:center;gap:14px;">
        <label style="font-size:13px;font-weight:600;color:#64748b;">{{ __('Enfant') }} :</label>
        <select wire:model.live="studentId" style="border:1px solid #dde3ea;border-radius:8px;padding:8px 12px;font-size:14px;background:#fff;color:#0f172a;min-width:200px;">
            @foreach($children as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
        @if($className)<span style="font-size:13px;color:#94a3b8;">{{ __('Classe :class', ['class' => $className]) }}</span>@endif
    </div>

    @if($empty)
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:48px;text-align:center;">
            <div style="font-size:38px;">🗓️</div>
            <div style="font-size:15px;font-weight:700;color:#1e293b;margin-top:10px;">{{ __('Emploi du temps non disponible') }}</div>
            <div style="font-size:13px;color:#94a3b8;margin-top:4px;">{{ __("L'emploi du temps de la classe n'a pas encore été planifié.") }}</div>
        </div>
    @else
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.05);overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:680px;">
            <thead>
                <tr style="background:#fafbfc;">
                    <th style="padding:12px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;text-align:start;border-bottom:1px solid #eaeef3;">{{ __('Jour') }}</th>
                    @foreach($slots as $s)
                    <th style="padding:12px 10px;font-size:11px;font-weight:700;color:#64748b;text-align:center;border-bottom:1px solid #eaeef3;border-inline-start:1px solid #f1f4f8;">{{ $s['start'] }}<br><span style="font-weight:500;color:#94a3b8;">{{ $s['end'] }}</span></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($grid as $row)
                <tr>
                    <td style="padding:12px 14px;font-size:13px;font-weight:700;color:#0f172a;border-bottom:1px solid #f1f4f8;background:#fafbfc;">{{ __($row['day']) }}</td>
                    @foreach($row['cells'] as $cell)
                    <td style="padding:7px;border-bottom:1px solid #f1f4f8;border-inline-start:1px solid #f1f4f8;vertical-align:top;">
                        @if($cell)
                        <div style="background:#eff6ff;border-inline-start:3px solid #2563eb;border-radius:8px;padding:7px 9px;">
                            <div style="font-size:12px;font-weight:700;color:#1e3a8a;">{{ $cell->subject?->name ?? '—' }}</div>
                            <div style="font-size:10.5px;color:#3b82f6;margin-top:2px;">{{ $cell->teacher?->full_name ?? '' }}</div>
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

</div>
@endif
</x-filament-panels::page>
