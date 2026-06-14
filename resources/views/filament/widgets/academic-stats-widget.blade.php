<x-filament-widgets::widget>
<style>
.aw-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;padding:20px}
@media(max-width:1100px){.aw-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:700px){.aw-grid{grid-template-columns:repeat(2,1fr)}}
.aw-card{background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;display:flex;align-items:center;gap:14px;transition:transform .15s,box-shadow .15s}
.aw-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.09)}
html.dark .aw-card{background:#1e293b;border-color:#334155}
.aw-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.aw-icon svg{width:22px;height:22px}
.aw-label{font-size:11.5px;color:#64748b;font-weight:500;letter-spacing:.3px;margin-bottom:3px;text-transform:uppercase}
html.dark .aw-label{color:#94a3b8}
.aw-value{font-size:22px;font-weight:700;color:#0f172a;line-height:1}
html.dark .aw-value{color:#f1f5f9}
</style>
<div class="aw-grid">
    @foreach($this->getStats() as $stat)
    <div class="aw-card">
        <div class="aw-icon" style="background:{{ $stat['bg'] }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="{{ $stat['color'] }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="{{ $stat['icon'] }}"/>
            </svg>
        </div>
        <div>
            <div class="aw-label">{{ $stat['label'] }}</div>
            <div class="aw-value">{{ $stat['value'] }}</div>
        </div>
    </div>
    @endforeach
</div>
</x-filament-widgets::widget>
