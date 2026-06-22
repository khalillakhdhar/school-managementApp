<x-filament-panels::page>
@assets
<style>
.ss-wrap{max-width:900px;margin:0 auto;padding:0 0 40px}
.ss-section{background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;margin-bottom:20px;overflow:hidden}
html.dark .ss-section{background:#1e293b;border-color:#334155}
.ss-section-header{padding:18px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:12px}
html.dark .ss-section-header{border-color:#334155}
.ss-section-icon{width:36px;height:36px;background:#eff6ff;border-radius:9px;display:flex;align-items:center;justify-content:center}
html.dark .ss-section-icon{background:#1e3a5f}
.ss-section-icon svg{width:18px;height:18px;color:#1d4ed8}
.ss-section-title{font-size:14px;font-weight:700;color:#0f172a}
html.dark .ss-section-title{color:#f1f5f9}
.ss-section-desc{font-size:12px;color:#64748b;margin-top:1px}
.ss-body{padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:640px){.ss-body{grid-template-columns:1fr}}
.ss-full{grid-column:1/-1}
.ss-label{font-size:12px;font-weight:600;color:#475569;margin-bottom:5px;display:block}
html.dark .ss-label{color:#94a3b8}
.ss-input{width:100%;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:9px;padding:9px 13px;font-size:14px;color:#0f172a;transition:border-color .15s,box-shadow .15s;outline:none;box-sizing:border-box}
.ss-input:focus{border-color:#1d4ed8;box-shadow:0 0 0 3px rgba(29,78,216,.1);background:#ffffff}
html.dark .ss-input{background:#0f172a;border-color:#334155;color:#f1f5f9}
html.dark .ss-input:focus{background:#1e293b}
.ss-textarea{resize:vertical;min-height:80px}
.ss-file-box{background:#f8fafc;border:2px dashed #e2e8f0;border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:border-color .15s}
html.dark .ss-file-box{background:#0f172a;border-color:#334155}
.ss-file-box:hover{border-color:#1d4ed8}
.ss-file-input{display:none}
.ss-file-label{font-size:12px;color:#64748b;margin-top:8px}
.ss-preview{max-width:80px;max-height:60px;border-radius:8px;margin:0 auto 8px;display:block;object-fit:contain}
.ss-save-bar{background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 24px;display:flex;justify-content:flex-end;gap:12px;align-items:center}
html.dark .ss-save-bar{background:#1e293b;border-color:#334155}
.ss-btn-save{background:#1d4ed8;color:#ffffff;border:none;border-radius:9px;padding:10px 28px;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s}
.ss-btn-save:hover{background:#1e40af}
</style>
@endassets

<div class="ss-wrap">

    {{-- Identity --}}
    <div class="ss-section">
        <div class="ss-section-header">
            <div class="ss-section-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/>
                </svg>
            </div>
            <div>
                <div class="ss-section-title">{{ __("Identité de l'établissement") }}</div>
                <div class="ss-section-desc">{{ __('Nom, slogan, type et année scolaire') }}</div>
            </div>
        </div>
        <div class="ss-body">
            <div>
                <label class="ss-label">{{ __("Nom de l'établissement") }} *</label>
                <input class="ss-input" type="text" wire:model="school_name" placeholder="EliteCampus">
            </div>
            <div>
                <label class="ss-label">{{ __("Type d'établissement") }}</label>
                <input class="ss-input" type="text" wire:model="school_type" placeholder="{{ __('École primaire / Lycée...') }}">
            </div>
            <div>
                <label class="ss-label">{{ __('Slogan') }}</label>
                <input class="ss-input" type="text" wire:model="slogan" placeholder="{{ __("L'excellence au service de l'avenir") }}">
            </div>
            <div>
                <label class="ss-label">{{ __('Année scolaire') }}</label>
                <input class="ss-input" type="text" wire:model="academic_year" placeholder="2025-2026">
            </div>
            <div class="ss-full">
                <label class="ss-label">{{ __('Description') }}</label>
                <textarea class="ss-input ss-textarea" wire:model="description" placeholder="{{ __("Présentation de l'établissement...") }}"></textarea>
            </div>
        </div>
    </div>

    {{-- Logo & Favicon --}}
    <div class="ss-section">
        <div class="ss-section-header">
            <div class="ss-section-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
                </svg>
            </div>
            <div>
                <div class="ss-section-title">{{ __('Logo et Favicon') }}</div>
                <div class="ss-section-desc">{{ __("Images de branding utilisées dans l'interface") }}</div>
            </div>
        </div>
        <div class="ss-body">
            <div>
                <label class="ss-label">{{ __('Logo (PNG, SVG recommandé)') }}</label>
                @if($existing_logo)
                <img class="ss-preview" src="{{ Storage::url($existing_logo) }}" alt="{{ __('Logo actuel') }}">
                @endif
                <input type="file" wire:model="logo" accept="image/*" class="ss-input" style="padding:6px">
            </div>
            <div>
                <label class="ss-label">{{ __('Favicon (ICO, PNG 32x32)') }}</label>
                @if($existing_favicon)
                <img class="ss-preview" src="{{ Storage::url($existing_favicon) }}" alt="{{ __('Favicon actuel') }}">
                @endif
                <input type="file" wire:model="favicon" accept="image/*" class="ss-input" style="padding:6px">
            </div>
        </div>
    </div>

    {{-- Contact --}}
    <div class="ss-section">
        <div class="ss-section-header">
            <div class="ss-section-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="ss-section-title">{{ __('Coordonnées') }}</div>
                <div class="ss-section-desc">{{ __('Adresse, téléphone, email et site web') }}</div>
            </div>
        </div>
        <div class="ss-body">
            <div class="ss-full">
                <label class="ss-label">{{ __('Adresse') }}</label>
                <input class="ss-input" type="text" wire:model="address" placeholder="12 Rue des Écoles">
            </div>
            <div>
                <label class="ss-label">{{ __('Ville') }}</label>
                <input class="ss-input" type="text" wire:model="city" placeholder="Tunis">
            </div>
            <div>
                <label class="ss-label">{{ __('Pays') }}</label>
                <input class="ss-input" type="text" wire:model="country" placeholder="Tunisie">
            </div>
            <div>
                <label class="ss-label">{{ __('Téléphone fixe') }}</label>
                <input class="ss-input" type="text" wire:model="phone" placeholder="+216 71 000 000">
            </div>
            <div>
                <label class="ss-label">{{ __('Mobile') }}</label>
                <input class="ss-input" type="text" wire:model="mobile" placeholder="+216 XX XXX XXX">
            </div>
            <div>
                <label class="ss-label">{{ __('Email') }}</label>
                <input class="ss-input" type="email" wire:model="email" placeholder="contact@ecole.tn">
            </div>
            <div>
                <label class="ss-label">{{ __('Site web') }}</label>
                <input class="ss-input" type="url" wire:model="website" placeholder="https://www.ecole.tn">
            </div>
        </div>
    </div>

    {{-- Social --}}
    <div class="ss-section">
        <div class="ss-section-header">
            <div class="ss-section-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 2H3v16l4-4h14V2zM9 8h6M9 12h4"/>
                </svg>
            </div>
            <div>
                <div class="ss-section-title">{{ __('Réseaux sociaux') }}</div>
                <div class="ss-section-desc">{{ __("Liens vers les pages officielles de l'établissement") }}</div>
            </div>
        </div>
        <div class="ss-body">
            <div>
                <label class="ss-label">Facebook</label>
                <input class="ss-input" type="url" wire:model="facebook" placeholder="https://facebook.com/...">
            </div>
            <div>
                <label class="ss-label">Instagram</label>
                <input class="ss-input" type="url" wire:model="instagram" placeholder="https://instagram.com/...">
            </div>
            <div>
                <label class="ss-label">LinkedIn</label>
                <input class="ss-input" type="url" wire:model="linkedin" placeholder="https://linkedin.com/company/...">
            </div>
            <div>
                <label class="ss-label">YouTube</label>
                <input class="ss-input" type="url" wire:model="youtube" placeholder="https://youtube.com/@...">
            </div>
        </div>
    </div>

    {{-- Save --}}
    <div class="ss-save-bar">
        <button class="ss-btn-save" wire:click="save" wire:loading.attr="disabled">
            <span wire:loading.remove>{{ __('Sauvegarder les paramètres') }}</span>
            <span wire:loading>{{ __('Sauvegarde...') }}</span>
        </button>
    </div>

</div>
</x-filament-panels::page>
