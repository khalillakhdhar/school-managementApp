<x-filament-widgets::widget>
<div style="background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 55%,#2563eb 100%);border-radius:16px;padding:28px 32px;position:relative;overflow:hidden;">

    {{-- Decorative circles --}}
    <div style="position:absolute;top:-50px;right:-50px;width:220px;height:220px;background:rgba(255,255,255,0.05);border-radius:50%;pointer-events:none;"></div>
    <div style="position:absolute;bottom:-70px;right:120px;width:160px;height:160px;background:rgba(255,255,255,0.04);border-radius:50%;pointer-events:none;"></div>
    <div style="position:absolute;top:20px;right:300px;width:80px;height:80px;background:rgba(255,255,255,0.03);border-radius:50%;pointer-events:none;"></div>

    {{-- Top row: greeting + school year --}}
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:24px;position:relative;">

        <div style="display:flex;align-items:center;gap:14px;">
            {{-- EC Icon --}}
            <div style="width:52px;height:52px;background:rgba(255,255,255,0.15);border-radius:14px;display:flex;align-items:center;justify-content:center;font-family:Inter,Arial,sans-serif;font-size:19px;font-weight:800;color:white;letter-spacing:-0.5px;border:1px solid rgba(255,255,255,0.25);flex-shrink:0;">EC</div>
            <div>
                <div style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:3px;">ELITECAMPUS</div>
                <div style="color:white;font-size:22px;font-weight:800;letter-spacing:-0.5px;line-height:1.2;">
                    Bonjour, {{ $userName }}!
                </div>
                <div style="color:rgba(255,255,255,0.6);font-size:13px;margin-top:3px;">
                    {{ ucfirst(now()->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}
                </div>
            </div>
        </div>

        <div style="text-align:right;flex-shrink:0;">
            <div style="color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;">Année scolaire</div>
            <div style="color:white;font-size:24px;font-weight:800;letter-spacing:-0.5px;margin-top:2px;">{{ $schoolYear }}</div>
            <div style="display:inline-flex;align-items:center;gap:5px;margin-top:6px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:20px;padding:3px 12px;">
                <span style="width:6px;height:6px;background:#4ade80;border-radius:50%;display:inline-block;"></span>
                <span style="color:rgba(255,255,255,0.85);font-size:11px;font-weight:600;">Système actif</span>
            </div>
        </div>
    </div>

    {{-- KPI tiles --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;position:relative;">

        {{-- Élèves actifs --}}
        <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:12px;padding:18px 20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <div style="color:rgba(255,255,255,0.65);font-size:11px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;">Élèves actifs</div>
                <div style="width:28px;height:28px;background:rgba(255,255,255,0.12);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </div>
            </div>
            <div style="color:white;font-size:30px;font-weight:800;letter-spacing:-1px;line-height:1;">{{ $activeStudents }}</div>
            <div style="color:rgba(255,255,255,0.5);font-size:11px;margin-top:5px;">/ {{ $totalStudents }} inscrits</div>
        </div>

        {{-- Recettes du mois --}}
        <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:12px;padding:18px 20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <div style="color:rgba(255,255,255,0.65);font-size:11px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;">Recettes du mois</div>
                <div style="width:28px;height:28px;background:rgba(74,222,128,0.2);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                    </svg>
                </div>
            </div>
            <div style="color:white;font-size:22px;font-weight:800;letter-spacing:-0.5px;line-height:1;">
                {{ $revenueMonth > 0 ? number_format($revenueMonth, 0, ',', ' ') : '0' }}
            </div>
            <div style="color:rgba(255,255,255,0.5);font-size:11px;margin-top:5px;">TND encaissés</div>
        </div>

        {{-- Paiements en retard --}}
        <div style="background:{{ $overdueCount > 0 ? 'rgba(248,113,113,0.15)' : 'rgba(255,255,255,0.1)' }};border:1px solid {{ $overdueCount > 0 ? 'rgba(248,113,113,0.3)' : 'rgba(255,255,255,0.15)' }};border-radius:12px;padding:18px 20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <div style="color:rgba(255,255,255,0.65);font-size:11px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;">Paiements en retard</div>
                <div style="width:28px;height:28px;background:rgba(248,113,113,0.2);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $overdueCount > 0 ? '#f87171' : 'rgba(255,255,255,0.8)' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
            </div>
            <div style="color:{{ $overdueCount > 0 ? '#fca5a5' : 'white' }};font-size:30px;font-weight:800;letter-spacing:-1px;line-height:1;">{{ $overdueCount }}</div>
            <div style="color:rgba(255,255,255,0.5);font-size:11px;margin-top:5px;">{{ $overdueCount === 0 ? 'Aucun impayé' : 'paiement(s) échu(s)' }}</div>
        </div>

        {{-- Incidents non notifiés --}}
        <div style="background:{{ $pendingIncidents > 0 ? 'rgba(251,191,36,0.15)' : 'rgba(255,255,255,0.1)' }};border:1px solid {{ $pendingIncidents > 0 ? 'rgba(251,191,36,0.3)' : 'rgba(255,255,255,0.15)' }};border-radius:12px;padding:18px 20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <div style="color:rgba(255,255,255,0.65);font-size:11px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;">Incidents en attente</div>
                <div style="width:28px;height:28px;background:rgba(251,191,36,0.2);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $pendingIncidents > 0 ? '#fbbf24' : 'rgba(255,255,255,0.8)' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>
            </div>
            <div style="color:{{ $pendingIncidents > 0 ? '#fde68a' : 'white' }};font-size:30px;font-weight:800;letter-spacing:-1px;line-height:1;">{{ $pendingIncidents }}</div>
            <div style="color:rgba(255,255,255,0.5);font-size:11px;margin-top:5px;">{{ $pendingIncidents === 0 ? 'Parents notifiés' : 'parent(s) non notifié(s)' }}</div>
        </div>

    </div>
</div>
</x-filament-widgets::widget>
