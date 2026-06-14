<!DOCTYPE html>
<html lang="fr">
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
        .severity-bar { background: #dc2626; padding: 14px 32px; display: flex; align-items: center; gap: 10px; }
        .severity-bar h2 { color: white; font-size: 15px; font-weight: 700; }
        .body { background: #f8fafc; padding: 32px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }
        .intro { font-size: 15px; color: #334155; line-height: 1.6; margin-bottom: 20px; }
        .box { background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin: 20px 0; }
        .field { margin-bottom: 16px; }
        .field:last-child { margin-bottom: 0; }
        .label { color: #64748b; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; }
        .value { color: #0f172a; font-size: 14px; font-weight: 500; }
        .severity-high   { color: #dc2626; font-weight: 700; }
        .severity-medium { color: #d97706; font-weight: 700; }
        .severity-low    { color: #16a34a; font-weight: 600; }
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

        <div class="severity-bar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <h2>Rapport d'Incident</h2>
        </div>

        <div class="body">
            <p class="intro">
                Bonjour <strong>{{ $parent->full_name }}</strong>,<br>
                Nous vous informons d'un incident concernant votre enfant <strong>{{ $incident->student?->full_name }}</strong>.
            </p>

            <div class="box">
                <div class="field">
                    <div class="label">Date de l'incident</div>
                    <div class="value">{{ $incident->incident_date->format('d/m/Y') }}</div>
                </div>
                <div class="field">
                    <div class="label">Type</div>
                    <div class="value">{{ $incident->title }}</div>
                </div>
                <div class="field">
                    <div class="label">Sévérité</div>
                    <div class="value severity-{{ $incident->severity }}">
                        {{ match($incident->severity) { 'high' => '🔴 Élevée', 'medium' => '🟡 Moyenne', default => '🟢 Faible' } }}
                    </div>
                </div>
                <div class="field">
                    <div class="label">Description</div>
                    <div class="value">{{ $incident->description }}</div>
                </div>
                @if($incident->action_taken)
                <div class="field">
                    <div class="label">Mesures prises</div>
                    <div class="value">{{ $incident->action_taken }}</div>
                </div>
                @endif
            </div>

            <p class="note">
                Nous restons disponibles pour tout renseignement complémentaire.<br>
                N'hésitez pas à contacter l'administration de l'établissement.
            </p>
        </div>

        <div class="footer">
            <div class="footer-brand">Elite<span>Campus</span> — Smart School Management Platform</div>
            <div class="footer-sub">by EliteTech Consulting · Ce message est automatique, merci de ne pas y répondre.</div>
        </div>
    </div>
</body>
</html>
