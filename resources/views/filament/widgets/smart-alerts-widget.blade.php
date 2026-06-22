<x-filament-widgets::widget>
<div style="background:white;border-radius:16px;border:1px solid #e8edf2;box-shadow:0 1px 4px rgba(0,0,0,0.06);overflow:hidden;">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9;">
        <div style="display:flex;align-items:center;gap:10px;">
            {{-- Bell icon --}}
            <div style="position:relative;display:inline-flex;">
                <div style="width:36px;height:36px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                </div>
                @if($totalCount > 0)
                <span style="position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;background:#ef4444;color:white;font-size:10px;font-weight:800;border-radius:10px;display:flex;align-items:center;justify-content:center;padding:0 4px;border:2px solid white;">{{ $totalCount }}</span>
                @endif
            </div>
            <div>
                <div style="font-size:15px;font-weight:700;color:#0f172a;">Centre de notifications</div>
                <div style="font-size:12px;color:#64748b;">
                    @if($totalCount > 0)
                        {{ $totalCount }} alerte{{ $totalCount > 1 ? 's' : '' }} active{{ $totalCount > 1 ? 's' : '' }}
                        @if($criticalCount > 0)
                            · <span style="color:#ef4444;font-weight:600;">{{ $criticalCount }} critique{{ $criticalCount > 1 ? 's' : '' }}</span>
                        @endif
                    @else
                        Tout est à jour
                    @endif
                </div>
            </div>
        </div>
        {{-- Priority summary pills --}}
        @if($totalCount > 0)
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            @if($criticalCount > 0)
            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#fff1f2;border:1px solid #fecdd3;border-radius:20px;font-size:11px;font-weight:700;color:#dc2626;">
                <span style="width:6px;height:6px;background:#ef4444;border-radius:50%;display:inline-block;"></span>
                {{ $criticalCount }} Critique{{ $criticalCount > 1 ? 's' : '' }}
            </span>
            @endif
            @if($warningCount > 0)
            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#fffbeb;border:1px solid #fde68a;border-radius:20px;font-size:11px;font-weight:700;color:#d97706;">
                <span style="width:6px;height:6px;background:#f59e0b;border-radius:50%;display:inline-block;"></span>
                {{ $warningCount }} Avert.
            </span>
            @endif
            @if($infoCount > 0)
            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:20px;font-size:11px;font-weight:700;color:#0284c7;">
                <span style="width:6px;height:6px;background:#0ea5e9;border-radius:50%;display:inline-block;"></span>
                {{ $infoCount }} Info
            </span>
            @endif
        </div>
        @endif
    </div>

    {{-- Body --}}
    @if($totalCount === 0)
    <div style="padding:40px 20px;text-align:center;">
        <div style="width:56px;height:56px;background:#f0fdf4;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">Tout est à jour !</div>
        <div style="font-size:13px;color:#64748b;">Aucune alerte active. Votre établissement fonctionne parfaitement.</div>
    </div>

    @else
    <div style="display:flex;flex-direction:column;gap:0;">
        @foreach($notifications as $i => $notif)
        @php
            $isLast = $i === count($notifications) - 1;
            $colors = match($notif['level']) {
                'critical' => ['accent'=>'#ef4444','bg'=>'#fff1f2','badge_bg'=>'#ef4444','text'=>'#b91c1c','icon_bg'=>'#fee2e2','label'=>'Critique','label_color'=>'#dc2626','label_bg'=>'#fff1f2','label_border'=>'#fecdd3'],
                'warning'  => ['accent'=>'#f59e0b','bg'=>'#fffbeb','badge_bg'=>'#f59e0b','text'=>'#92400e','icon_bg'=>'#fef3c7','label'=>'Avert.','label_color'=>'#d97706','label_bg'=>'#fffbeb','label_border'=>'#fde68a'],
                default    => ['accent'=>'#0ea5e9','bg'=>'#f0f9ff','badge_bg'=>'#0ea5e9','text'=>'#0369a1','icon_bg'=>'#e0f2fe','label'=>'Info','label_color'=>'#0284c7','label_bg'=>'#f0f9ff','label_border'=>'#bae6fd'],
            };
        @endphp
        <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 20px;border-inline-start:3px solid {{ $colors['accent'] }};{{ !$isLast ? 'border-bottom:1px solid #f1f5f9;' : '' }}background:{{ $notif['level'] === 'critical' ? '#fffafa' : 'white' }};">
            {{-- Icon --}}
            <div style="width:36px;height:36px;background:{{ $colors['icon_bg'] }};border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                @if($notif['icon'] === 'banknote')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $colors['text'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                @elseif($notif['icon'] === 'triangle-alert')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $colors['text'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                @elseif($notif['icon'] === 'alert-circle')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $colors['text'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                @elseif($notif['icon'] === 'building-2')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $colors['text'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                @elseif($notif['icon'] === 'wallet')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $colors['text'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
                @elseif($notif['icon'] === 'file-text')
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $colors['text'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                @else
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $colors['text'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a4 4 0 00-4 4"/><line x1="17" y1="17" x2="21" y2="21"/><path d="M17 17a4 4 0 00-4-4H9"/></svg>
                @endif
            </div>
            {{-- Content --}}
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;flex-wrap:wrap;">
                    <span style="font-size:13px;font-weight:700;color:#0f172a;">{{ $notif['title'] }}</span>
                    <span style="padding:1px 7px;background:{{ $colors['label_bg'] }};border:1px solid {{ $colors['label_border'] }};border-radius:12px;font-size:10px;font-weight:700;color:{{ $colors['label_color'] }};">{{ $colors['label'] }}</span>
                </div>
                <div style="font-size:12px;color:#64748b;margin-bottom:6px;">{{ $notif['description'] }}</div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <a href="{{ $notif['action_url'] }}" style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:600;color:{{ $colors['text'] }};text-decoration:none;padding:3px 10px;border:1px solid {{ $colors['accent'] }}33;border-radius:6px;background:{{ $colors['icon_bg'] }};transition:opacity .12s;" onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                        {{ $notif['action_label'] }}
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="{{ $colors['text'] }}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                    <span style="font-size:11px;color:#94a3b8;">{{ $notif['time'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
</x-filament-widgets::widget>
