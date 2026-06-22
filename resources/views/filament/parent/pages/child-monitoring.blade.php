<x-filament-panels::page>
@if(empty($children))
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:24px;color:#92400e;">
        <strong>{{ __('Aucun enfant rattaché à votre compte.') }}</strong>
    </div>
@else
<div style="display:flex;flex-direction:column;gap:18px;">
    @foreach($children as $child)
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;">
            <div>
                <div style="font-size:16px;font-weight:800;color:#0f172a;">{{ $child['name'] }}</div>
                <div style="font-size:12.5px;color:#64748b;">{{ __('Classe :class', ['class' => $child['class']]) }} · {{ now()->locale(app()->getLocale())->isoFormat('MMMM YYYY') }}</div>
            </div>
            @if($child['rate'] !== null)
            <div style="text-align:right;">
                <div style="font-size:26px;font-weight:800;color:{{ $child['rate']>=90 ? '#059669' : ($child['rate']>=75 ? '#b45309':'#dc2626') }};">{{ $child['rate'] }}%</div>
                <div style="font-size:11px;color:#94a3b8;font-weight:600;">{{ __('PRÉSENCE') }}</div>
            </div>
            @endif
        </div>

        {{-- Présence détaillée --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px;">
            @foreach([[__('Présent'),$child['present'],'#10b981','#ecfdf5'],[__('Absent'),$child['absent'],'#ef4444','#fef2f2'],[__('Retard'),$child['late'],'#f59e0b','#fffbeb']] as $k)
            <div style="background:{{ $k[3] }};border-radius:11px;padding:12px 16px;">
                <div style="font-size:22px;font-weight:800;color:{{ $k[2] }};">{{ $k[1] }}</div>
                <div style="font-size:11.5px;font-weight:600;color:#64748b;margin-top:2px;">{{ __(':label(s) ce mois', ['label' => $k[0]]) }}</div>
            </div>
            @endforeach
        </div>

        {{-- Incidents --}}
        <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:10px;">{{ __('Incidents récents') }}</div>
        @if(empty($child['incidents']))
            <div style="display:flex;align-items:center;gap:8px;color:#059669;font-size:13px;background:#ecfdf5;border-radius:10px;padding:12px 16px;">
                ✅ {{ __('Aucun incident signalé. Tout va bien !') }}
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($child['incidents'] as $inc)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:#f8fafc;border-radius:10px;border-left:3px solid {{ $inc['severity']==='high' ? '#ef4444' : ($inc['severity']==='medium' ? '#f59e0b':'#94a3b8') }};">
                    <div style="flex:1;">
                        <div style="font-size:13.5px;font-weight:600;color:#0f172a;">{{ $inc['title'] }}</div>
                        <div style="font-size:11.5px;color:#94a3b8;">{{ $inc['date'] }}</div>
                    </div>
                    <span style="font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:7px;
                        background:{{ $inc['severity']==='high' ? '#fef2f2' : ($inc['severity']==='medium' ? '#fffbeb':'#f1f5f9') }};
                        color:{{ $inc['severity']==='high' ? '#dc2626' : ($inc['severity']==='medium' ? '#b45309':'#64748b') }};">
                        {{ ['high'=>__('High'),'medium'=>__('Medium'),'low'=>__('Low')][$inc['severity']] ?? $inc['severity'] }}
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>
    @endforeach
</div>
@endif
</x-filament-panels::page>
