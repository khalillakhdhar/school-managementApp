<x-filament-panels::page>
@unless($employee)
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:24px;color:#92400e;">
        <strong>{{ __('Aucun profil employé lié à ce compte.') }}</strong>
        <div style="margin-top:6px;font-size:13px;">{{ __("Contactez l'administration pour rattacher votre compte à votre fiche employé.") }}</div>
    </div>
@else
<div style="display:flex;flex-direction:column;gap:18px;">

    <div style="border-radius:16px;padding:24px 28px;color:#fff;background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 8px 24px rgba(37,99,235,.3);">
        <div style="font-size:13px;opacity:.85;font-weight:600;">{{ $employee->is_teacher ? __('Enseignant(e)') : $employee->position }}</div>
        <div style="font-size:24px;font-weight:800;letter-spacing:-.4px;margin-top:2px;">{{ __('Bonjour, :name', ['name' => $employee->first_name]) }} 👋</div>
        <div style="font-size:13.5px;opacity:.9;margin-top:4px;">{{ now()->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY') }}</div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
        @foreach([
            [__('Séances / semaine'), $stats['sessions'], '#2563eb'],
            [__('Heures / semaine'), $stats['hours'].'h', '#10b981'],
            [__('Mes classes'), $stats['classes'], '#8b5cf6'],
            [__('Mes matières'), $stats['subjects'], '#f59e0b'],
        ] as $card)
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:18px 20px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
            <div style="font-size:12.5px;color:#64748b;font-weight:600;">{{ $card[0] }}</div>
            <div style="font-size:28px;font-weight:800;color:#0f172a;letter-spacing:-.6px;margin-top:6px;">{{ $card[1] }}</div>
            <div style="height:4px;border-radius:4px;background:{{ $card[2] }};margin-top:12px;opacity:.25;"></div>
        </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;">
        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
            <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 14px;">{{ __("Cours d'aujourd'hui — :day", ['day' => __($todayName)]) }}</h3>
            @forelse($today as $c)
            <div style="display:flex;align-items:center;gap:14px;padding:11px 14px;background:#f8fafc;border-radius:10px;margin-bottom:8px;">
                <div style="font-size:13px;font-weight:700;color:#2563eb;min-width:96px;">{{ $c['start'] }} – {{ $c['end'] }}</div>
                <div style="flex:1;">
                    <div style="font-size:14px;font-weight:600;color:#0f172a;">{{ $c['subject'] }}</div>
                    <div style="font-size:12px;color:#64748b;">{{ __('Classe :class', ['class' => $c['class']]) }} · {{ $c['room'] ?? __('Salle —') }}</div>
                </div>
            </div>
            @empty
            <div style="color:#94a3b8;font-size:13px;padding:14px 0;">{{ __("Aucun cours programmé aujourd'hui.") }}</div>
            @endforelse
        </div>

        <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:20px 24px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
            <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 14px;">{{ __('Mes dernières fiches de paie') }}</h3>
            @forelse($payslips as $p)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                <span style="font-size:13.5px;color:#1e293b;font-weight:600;">{{ $p['period'] }}</span>
                <span style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:13.5px;font-weight:700;color:#0f172a;">{{ number_format($p['net'],3) }} TND</span>
                    <span style="font-size:11px;font-weight:700;padding:2px 9px;border-radius:7px;background:{{ $p['status']==='paid' ? '#ecfdf5' : '#fffbeb' }};color:{{ $p['status']==='paid' ? '#059669' : '#b45309' }};">{{ $p['status']==='paid' ? __('Payée') : __('En attente') }}</span>
                </span>
            </div>
            @empty
            <div style="color:#94a3b8;font-size:13px;padding:14px 0;">{{ __('Aucune fiche de paie disponible.') }}</div>
            @endforelse
        </div>
    </div>

</div>
@endunless
</x-filament-panels::page>
