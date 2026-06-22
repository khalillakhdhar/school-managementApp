<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', Arial, sans-serif; color: #1e293b; background: #f1f5f9; padding: 32px 16px; }
        .wrapper { max-width: 600px; margin: 0 auto; }
        .brand-header { background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%); padding: 24px 32px; border-radius: 12px 12px 0 0; display: flex; align-items: center; gap: 16px; }
        .brand-icon { width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; color: white; letter-spacing: -0.5px; flex-shrink: 0; }
        .brand-name { color: white; font-size: 18px; font-weight: 800; letter-spacing: -0.3px; }
        .brand-sub { color: rgba(255,255,255,0.65); font-size: 11px; font-weight: 500; letter-spacing: 0.5px; }
        .alert-bar { background: #d97706; padding: 14px 32px; display: flex; align-items: center; gap: 10px; }
        .alert-bar h2 { color: white; font-size: 15px; font-weight: 700; }
        .body { background: #f8fafc; padding: 32px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }
        .intro { font-size: 15px; color: #334155; line-height: 1.6; margin-bottom: 20px; }
        .box { background: white; border: 1px solid #fde68a; border-radius: 10px; padding: 24px; margin: 20px 0; }
        .field { margin-bottom: 16px; }
        .field:last-child { margin-bottom: 0; }
        .label { color: #64748b; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; }
        .amount { font-size: 32px; font-weight: 800; color: #dc2626; letter-spacing: -1px; }
        .currency { font-size: 16px; font-weight: 600; color: #ef4444; }
        .value { color: #0f172a; font-size: 14px; font-weight: 500; }
        .overdue { color: #dc2626; font-weight: 700; }
        .warning-box { background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 18px; margin-top: 16px; font-size: 13px; color: #92400e; line-height: 1.5; }
        .note { font-size: 13px; color: #64748b; line-height: 1.6; margin-top: 20px; }
        .footer { background: #0f172a; padding: 20px 32px; border-radius: 0 0 12px 12px; }
        .footer-brand { color: white; font-size: 13px; font-weight: 700; }
        .footer-brand span { color: #60a5fa; }
        .footer-sub { color: #475569; font-size: 11px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="brand-header">
            <div class="brand-icon">EC</div>
            <div>
                <div class="brand-name">Elite<span style="color:#93c5fd;">Campus</span></div>
                <div class="brand-sub">SMART SCHOOL MANAGEMENT</div>
            </div>
        </div>

        <div class="alert-bar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <h2>{{ __('Rappel de Paiement') }}</h2>
        </div>

        <div class="body">
            <p class="intro">
                {{ __('Bonjour') }} <strong>{{ $parent->full_name }}</strong>,<br>
                {{ __('Nous vous rappelons qu\'un paiement est en attente pour votre enfant :student.', ['student' => $payment->student?->full_name]) }}
            </p>

            <div class="box">
                <div class="field">
                    <div class="label">{{ __('Montant dû') }}</div>
                    <div class="amount">{{ number_format($payment->amount, 3) }} <span class="currency">TND</span></div>
                </div>
                <div class="field">
                    <div class="label">{{ __("Date d'échéance") }}</div>
                    <div class="value">{{ $payment->due_date?->format('d/m/Y') ?? __('Non définie') }}</div>
                </div>
                @if($daysOverdue > 0)
                <div class="field">
                    <div class="label">{{ __('Retard') }}</div>
                    <div class="value overdue">{{ __(':n jour(s) de retard', ['n' => $daysOverdue]) }}</div>
                </div>
                @endif
            </div>

            @if($daysOverdue >= 30)
            <div class="warning-box">
                ⚠️ <strong>{{ __('Attention') }} :</strong> {{ __('Au-delà de 45 jours de retard, certains services pourraient être suspendus. Veuillez régulariser votre situation dans les plus brefs délais.') }}
            </div>
            @endif

            <p class="note" style="margin-top: 20px;">
                {{ __('Veuillez procéder au règlement dans les meilleurs délais ou contacter l\'administration pour tout arrangement de paiement.') }}
            </p>
        </div>

        <div class="footer">
            <div class="footer-brand">Elite<span>Campus</span> — Smart School Management Platform</div>
            <div class="footer-sub">by EliteTech Consulting · {{ __('Ce message est automatique, merci de ne pas y répondre.') }}</div>
        </div>
    </div>
</body>
</html>
