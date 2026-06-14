@php
    $year = now()->month >= 9
        ? now()->year . '–' . (now()->year + 1)
        : (now()->year - 1) . '–' . now()->year;
@endphp
<div style="padding:8px 8px 4px;">
    <div style="display:flex;align-items:center;gap:9px;padding:9px 10px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:9px;">
        <img src="{{ asset('images/logo transparent.png') }}"
             alt="Logo"
             style="width:28px;height:28px;object-fit:contain;border-radius:6px;flex-shrink:0;opacity:0.9;">
        <div style="min-width:0;flex:1;overflow:hidden;">
            <div style="font-size:12px;font-weight:700;color:#e2e8f0;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ config('app.name', 'EliteCampus') }}
            </div>
            <div style="font-size:10px;color:#475569;margin-top:2px;letter-spacing:0.2px;">
                Année scolaire {{ $year }}
            </div>
        </div>
    </div>
</div>
