<x-filament-panels::page>
<div style="display:flex;flex-direction:column;gap:18px;max-width:880px;">

    {{-- Status banner --}}
    <div style="border-radius:16px;padding:24px 28px;color:#fff;
        background:{{ $active ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#2563eb,#1d4ed8)' }};
        box-shadow:0 8px 24px {{ $active ? 'rgba(16,185,129,.3)' : 'rgba(37,99,235,.3)' }};">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:26px;">
                {{ $active ? '✅' : '✨' }}
            </div>
            <div>
                <div style="font-size:20px;font-weight:800;letter-spacing:-.3px;">
                    {{ $active ? __('Mode démo activé') : __('Mode démo désactivé') }}
                </div>
                <div style="font-size:14px;opacity:.9;margin-top:2px;">
                    {{ $active
                        ? __('La base contient les données de démonstration « École Privée El Amana ».')
                        : __("Activez le mode démo pour explorer l'application avec des données réalistes.") }}
                </div>
            </div>
        </div>
    </div>

    {{-- What's included --}}
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:22px 26px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 16px;">{{ __('Contenu de la démonstration') }}</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;">
            @php
            $items = [
                ['🎓',__('Élèves & classes'),__('~48 élèves répartis sur 8 classes (1ère→6ème année)')],
                ['👨‍🏫',__('Personnel'),__('12 enseignants + 3 administratifs avec CNSS, RIB…')],
                ['📚',__('Matières'),__('10 matières tunisiennes (Arabe, Français, Maths…)')],
                ['🗓️',__('Emplois du temps'),__('Grille hebdomadaire complète et identique par classe')],
                ['💳',__('Paiements'),__('Scolarité mensuelle (payés, en attente, en retard)')],
                ['👨‍👩‍👧',__('Parents'),__('Un responsable payeur par élève')],
                ['🧾',__('Dépenses'),__('Loyer, STEG, SONEDE, fournitures… sur 6 mois')],
                ['📊',__('Paie & présences'),__('Fiches de paie + pointages des 10 derniers jours')],
                ['⚠️',__('Incidents & blog'),__('Incidents élèves et annonces pour les parents')],
            ];
            @endphp
            @foreach($items as $it)
            <div style="display:flex;gap:11px;align-items:flex-start;">
                <span style="font-size:20px;line-height:1;">{{ $it[0] }}</span>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:#1e293b;">{{ $it[1] }}</div>
                    <div style="font-size:12px;color:#64748b;margin-top:1px;">{{ $it[2] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Demo login credentials (only when active) --}}
    @if($active)
    <div style="background:#fff;border:1px solid #e5e9f0;border-radius:14px;padding:22px 26px;box-shadow:0 1px 3px rgba(16,24,40,.05);">
        <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 14px;">{{ __('Comptes de démonstration') }}</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;">
            <div style="border:1px solid #eaeef3;border-radius:11px;padding:14px 16px;">
                <div style="font-size:12px;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:.5px;">{{ __('Enseignant') }}</div>
                <div style="font-size:13px;color:#1e293b;margin-top:6px;">{{ __('URL') }} : <code>/staff/login</code></div>
                <div style="font-size:13px;color:#1e293b;">Email : <code>salimwhichi@elamana.tn</code></div>
                <div style="font-size:13px;color:#1e293b;">{{ __('Mot de passe') }} : <code>demo1234</code></div>
            </div>
            <div style="border:1px solid #eaeef3;border-radius:11px;padding:14px 16px;">
                <div style="font-size:12px;font-weight:700;color:#10b981;text-transform:uppercase;letter-spacing:.5px;">{{ __('Parent') }}</div>
                <div style="font-size:13px;color:#1e293b;margin-top:6px;">{{ __('URL') }} : <code>/parent/login</code></div>
                <div style="font-size:13px;color:#1e293b;">Email : <code>parent1@elamana.tn</code></div>
                <div style="font-size:13px;color:#1e293b;">{{ __('Mot de passe') }} : <code>demo1234</code></div>
            </div>
            <div style="border:1px solid #eaeef3;border-radius:11px;padding:14px 16px;">
                <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">{{ __('Administrateur') }}</div>
                <div style="font-size:13px;color:#1e293b;margin-top:6px;">{{ __('URL') }} : <code>/admin/login</code></div>
                <div style="font-size:13px;color:#64748b;">{{ __('Votre compte admin habituel.') }}</div>
            </div>
        </div>
        <div style="font-size:12px;color:#94a3b8;margin-top:12px;">{{ __('12 enseignants & 8 parents ont un accès (tous : mot de passe') }} <code>demo1234</code>)</div>
    </div>
    @endif

    {{-- Hint --}}
    <div style="display:flex;gap:10px;align-items:flex-start;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:14px 18px;">
        <span style="font-size:16px;">💡</span>
        <div style="font-size:13px;color:#92400e;">
            {{ __('Utilisez les boutons en haut à droite :') }} <strong>{{ __('Activer le mode démo') }}</strong> {{ __('pour remplir la base, ou') }}
            <strong>{{ __('Supprimer les données démo') }}</strong> {{ __('pour tout effacer. Le compte administrateur est toujours conservé.') }}
        </div>
    </div>

</div>
</x-filament-panels::page>
