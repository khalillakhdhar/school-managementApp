<x-filament-panels::page>
@if(empty($posts))
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:48px;text-align:center;">
        <div style="font-size:38px;">📣</div>
        <div style="font-size:15px;font-weight:700;color:#1e293b;margin-top:10px;">{{ __('Aucune annonce pour le moment') }}</div>
        <div style="font-size:13px;color:#94a3b8;margin-top:4px;">{{ __("Les actualités de l'établissement apparaîtront ici.") }}</div>
    </div>
@else
<div style="display:flex;flex-direction:column;gap:16px;max-width:760px;">
    @foreach($posts as $post)
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:22px 26px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <div style="width:38px;height:38px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">📣</div>
            <div>
                <div style="font-size:16px;font-weight:800;color:#0f172a;line-height:1.25;">{{ $post['title'] }}</div>
                <div style="font-size:12px;color:#94a3b8;">{{ $post['date'] }}</div>
            </div>
        </div>
        <div style="font-size:13.5px;color:#475569;line-height:1.6;">{{ $post['content'] }}</div>
    </div>
    @endforeach
</div>
@endif
</x-filament-panels::page>
