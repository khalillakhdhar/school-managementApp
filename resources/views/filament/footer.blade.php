@php
use App\Models\SchoolSetting;
$s = SchoolSetting::getInstance();
$year = now()->year;
$academicYear = $s->academic_year ?? (now()->month >= 9 ? "{$year}-" . ($year+1) : ($year-1) . "-{$year}");
@endphp
<style>
.ec-footer{background:#0f172a;border-top:1px solid #1e293b;padding:24px 32px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-top:auto}
.ec-footer-left{display:flex;align-items:center;gap:12px}
.ec-footer-logo{height:32px;width:auto;object-fit:contain;border-radius:6px}
.ec-footer-name{color:#cbd5e1;font-size:13.5px;font-weight:600}
.ec-footer-year{color:#475569;font-size:11.5px;margin-top:2px}
.ec-footer-center{color:#475569;font-size:12px;text-align:center}
.ec-footer-center a{color:#64748b;text-decoration:none;transition:color .15s}
.ec-footer-center a:hover{color:#94a3b8}
.ec-footer-right{display:flex;align-items:center;gap:12px}
.ec-footer-social a{display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:7px;background:#1e293b;color:#64748b;transition:background .15s,color .15s;text-decoration:none}
.ec-footer-social a:hover{background:#1d4ed8;color:#ffffff}
.ec-footer-social a svg{width:14px;height:14px}
</style>
<footer class="ec-footer">
    <div class="ec-footer-left">
        @if($s->logo)
        <img class="ec-footer-logo" src="{{ Storage::url($s->logo) }}" alt="{{ $s->school_name }}">
        @else
        <div style="width:32px;height:32px;background:#1d4ed8;border-radius:8px;display:flex;align-items:center;justify-content:center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
            </svg>
        </div>
        @endif
        <div>
            <div class="ec-footer-name">{{ $s->school_name ?? config('app.name') }}</div>
            <div class="ec-footer-year">Année scolaire {{ $academicYear }}</div>
        </div>
    </div>

    <div class="ec-footer-center">
        @if($s->address || $s->city)
        <div>{{ implode(', ', array_filter([$s->address, $s->city, $s->country])) }}</div>
        @endif
        @if($s->email || $s->phone)
        <div style="margin-top:4px">
            @if($s->email)<a href="mailto:{{ $s->email }}">{{ $s->email }}</a>@endif
            @if($s->email && $s->phone) &nbsp;·&nbsp; @endif
            @if($s->phone){{ $s->phone }}@endif
        </div>
        @endif
        <div style="margin-top:6px;color:#334155;font-size:11px">
            &copy; {{ now()->year }} {{ $s->school_name ?? config('app.name') }} — Tous droits réservés
        </div>
    </div>

    <div class="ec-footer-right ec-footer-social" style="display:flex;gap:8px">
        @if($s->facebook)
        <a href="{{ $s->facebook }}" target="_blank" title="Facebook">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
        </a>
        @endif
        @if($s->instagram)
        <a href="{{ $s->instagram }}" target="_blank" title="Instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        </a>
        @endif
        @if($s->linkedin)
        <a href="{{ $s->linkedin }}" target="_blank" title="LinkedIn">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
        </a>
        @endif
        @if($s->youtube)
        <a href="{{ $s->youtube }}" target="_blank" title="YouTube">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="#0f172a"/></svg>
        </a>
        @endif
        @if($s->website)
        <a href="{{ $s->website }}" target="_blank" title="Site web">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
        </a>
        @endif
    </div>
</footer>
