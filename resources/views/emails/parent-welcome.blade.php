<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', Arial, sans-serif; color: #1e293b; background: #f1f5f9; padding: 32px 16px; }
        .wrapper { max-width: 600px; margin: 0 auto; }
        .brand-header { background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%); padding: 28px 32px; border-radius: 12px 12px 0 0; text-align: center; }
        .brand-icon { width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; color: white; letter-spacing: -0.5px; margin-bottom: 12px; }
        .brand-name { color: white; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        .brand-sub { color: rgba(255,255,255,0.7); font-size: 12px; font-weight: 500; margin-top: 4px; }
        .portal-badge { display: inline-block; background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.9); font-size: 11px; font-weight: 600; letter-spacing: 1.5px; padding: 4px 12px; border-radius: 20px; margin-top: 12px; border: 1px solid rgba(255,255,255,0.25); }
        .body { background: #f8fafc; padding: 32px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }
        .intro { font-size: 15px; color: #334155; line-height: 1.6; margin-bottom: 24px; }
        .box { background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin: 20px 0; }
        .box h3 { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 16px; }
        .credential-row { margin-bottom: 14px; }
        .cred-label { color: #64748b; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 5px; }
        .credential { font-family: 'Courier New', monospace; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 8px 14px; border-radius: 6px; font-size: 14px; color: #0f172a; display: block; font-weight: 600; }
        .warning-box { background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; margin-top: 12px; font-size: 13px; color: #92400e; }
        .btn { display: inline-block; background: linear-gradient(135deg, #1d4ed8, #1e40af); color: white; padding: 13px 28px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 700; margin-top: 8px; letter-spacing: -0.2px; }
        .note { font-size: 12px; color: #94a3b8; line-height: 1.6; margin-top: 20px; }
        .footer { background: #0f172a; padding: 20px 32px; border-radius: 0 0 12px 12px; text-align: center; }
        .footer-brand { color: white; font-size: 13px; font-weight: 700; }
        .footer-brand span { color: #60a5fa; }
        .footer-sub { color: #475569; font-size: 11px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="brand-header">
            <div class="brand-icon">EC</div>
            <div class="brand-name">Elite<span style="color:#93c5fd;">Campus</span></div>
            <div class="brand-sub">Smart School Management Platform</div>
            <div class="portal-badge">{{ __('PORTAIL PARENTS') }}</div>
        </div>

        <div class="body">
            <p class="intro">
                {{ __('Bonjour') }} <strong>{{ $parent->full_name }}</strong>,<br><br>
                {{ __("Votre compte d'accès au portail parents EliteCampus a été créé. Vous pouvez désormais suivre la scolarité et les paiements de vos enfants depuis n'importe quel appareil.") }}
            </p>

            <div class="box">
                <h3>🔐 {{ __('Vos identifiants de connexion') }}</h3>
                <div class="credential-row">
                    <div class="cred-label">{{ __('Adresse email') }}</div>
                    <code class="credential">{{ $parent->email }}</code>
                </div>
                <div class="credential-row">
                    <div class="cred-label">{{ __('Mot de passe temporaire') }}</div>
                    <code class="credential">{{ $temporaryPassword }}</code>
                </div>
                <div class="warning-box">
                    ⚠️ <strong>{{ __('Important') }} :</strong> {{ __('Veuillez modifier votre mot de passe dès votre première connexion pour sécuriser votre compte.') }}
                </div>
            </div>

            <a href="{{ $loginUrl }}" class="btn">{{ __('Accéder au portail') }} →</a>

            <p class="note" style="margin-top: 24px;">
                {{ __("Si vous n'êtes pas à l'origine de cette demande, veuillez contacter l'administration de l'établissement immédiatement.") }}
            </p>
        </div>

        <div class="footer">
            <div class="footer-brand">Elite<span>Campus</span> — Smart School Management Platform</div>
            <div class="footer-sub">by EliteTech Consulting · {{ __('Ce message est automatique, merci de ne pas y répondre.') }}</div>
        </div>
    </div>
</body>
</html>
