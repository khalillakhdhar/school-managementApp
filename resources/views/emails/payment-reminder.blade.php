<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #d97706; color: white; padding: 24px; border-radius: 8px 8px 0 0; text-align: center; }
        .body   { background: #f8fafc; padding: 32px; border: 1px solid #e2e8f0; }
        .footer { background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
        .box    { background: white; border: 1px solid #fde68a; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .amount { font-size: 28px; font-weight: bold; color: #dc2626; }
        .label  { color: #64748b; font-size: 13px; margin-bottom: 4px; }
        .warning { background: #fef3c7; border: 1px solid #fde68a; padding: 12px; border-radius: 6px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>🔔 Rappel de Paiement</h2>
    </div>
    <div class="body">
        <p>Bonjour <strong>{{ $parent->full_name }}</strong>,</p>
        <p>Nous vous rappelons qu'un paiement est en attente pour votre enfant <strong>{{ $payment->student?->full_name }}</strong>.</p>

        <div class="box">
            <div class="label">Montant dû</div>
            <p class="amount">{{ number_format($payment->amount, 3) }} TND</p>

            <div class="label">Date d'échéance</div>
            <p>{{ $payment->due_date?->format('d/m/Y') ?? 'Non définie' }}</p>

            @if($daysOverdue > 0)
            <div class="label">Retard</div>
            <p style="color: #dc2626; font-weight: bold;">{{ $daysOverdue }} jour(s) de retard</p>
            @endif
        </div>

        @if($daysOverdue >= 30)
        <div class="warning">
            ⚠️ <strong>Attention :</strong> Au-delà de 45 jours de retard, certains services pourraient être suspendus.
        </div>
        @endif

        <p style="margin-top: 20px;">Veuillez procéder au règlement dans les meilleurs délais ou contacter l'administration pour tout arrangement.</p>
    </div>
    <div class="footer">
        <p>EduManage — Système de gestion scolaire</p>
    </div>
</body>
</html>
