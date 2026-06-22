<x-filament-panels::page>
@if($view === 'detail')
    {{-- ── Roster of one class ─────────────────────────────────────── --}}
    <div style="margin-bottom:16px;">
        <button wire:click="backToList" type="button" style="display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #e5e9f0;border-radius:9px;padding:8px 16px;font-size:13px;font-weight:600;color:#475569;cursor:pointer;">
            ← {{ __('Retour à mes classes') }}
        </button>
    </div>
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        <div style="padding:18px 22px;border-bottom:1px solid #f1f4f8;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:17px;font-weight:800;color:#0f172a;">{{ __('Classe :class', ['class' => $class->name]) }}</div>
                <div style="font-size:13px;color:#64748b;">{{ $class->level?->name }} · {{ __(':count élève(s)', ['count' => $students->count()]) }}</div>
            </div>
        </div>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#fafbfc;">
                    <th style="text-align:start;padding:12px 22px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">{{ __('Élève') }}</th>
                    <th style="text-align:start;padding:12px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">{{ __('N°') }}</th>
                    <th style="text-align:center;padding:12px 14px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">{{ __('Statut') }}</th>
                    <th style="text-align:end;padding:12px 22px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">{{ __('Présence (mois)') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                <tr style="border-top:1px solid #f1f4f8;">
                    <td style="padding:13px 22px;font-size:13.5px;font-weight:600;color:#0f172a;">{{ $s['name'] }}</td>
                    <td style="padding:13px 14px;font-size:12.5px;color:#64748b;">{{ $s['id_num'] }}</td>
                    <td style="padding:13px 14px;text-align:center;">
                        <span style="font-size:11px;font-weight:700;padding:2px 9px;border-radius:7px;background:{{ $s['status']==='active' ? '#ecfdf5':'#f1f5f9' }};color:{{ $s['status']==='active' ? '#059669':'#64748b' }};">{{ $s['status']==='active' ? __('Active'):__('Inactive') }}</span>
                    </td>
                    <td style="padding:13px 22px;text-align:end;font-size:13px;font-weight:700;color:{{ $s['rate']===null ? '#94a3b8' : ($s['rate']>=90 ? '#059669' : ($s['rate']>=75 ? '#b45309' : '#dc2626')) }};">
                        {{ $s['rate']===null ? '—' : $s['rate'].'%' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    {{-- ── List of my classes ──────────────────────────────────────── --}}
    @if($classes->isEmpty())
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:24px;color:#92400e;">
            <strong>{{ __('Aucune classe rattachée à votre compte.') }}</strong>
        </div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:18px;">
        @foreach($classes as $c)
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
                <div>
                    <div style="font-size:18px;font-weight:800;color:#0f172a;">{{ $c['name'] }}</div>
                    <div style="font-size:12.5px;color:#64748b;">{{ $c['level'] }}</div>
                </div>
                @if($c['titulaire'])
                <span style="font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:7px;background:#eff6ff;color:#2563eb;">{{ __('TITULAIRE') }}</span>
                @endif
            </div>
            <div style="display:flex;gap:18px;margin-bottom:14px;">
                <div><div style="font-size:20px;font-weight:800;color:#0f172a;">{{ $c['students'] }}</div><div style="font-size:11px;color:#94a3b8;font-weight:600;">{{ __('ÉLÈVES') }}</div></div>
            </div>
            @if($c['subjects'])
            <div style="font-size:12px;color:#475569;margin-bottom:16px;line-height:1.4;">📘 {{ $c['subjects'] }}</div>
            @endif
            <button wire:click="selectClass({{ $c['id'] }})" type="button" style="width:100%;background:#2563eb;color:#fff;border:none;border-radius:9px;padding:9px;font-size:13px;font-weight:600;cursor:pointer;">
                {{ __('Voir les élèves') }}
            </button>
        </div>
        @endforeach
    </div>
    @endif
@endif
</x-filament-panels::page>
