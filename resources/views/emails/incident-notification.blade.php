<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc2626; color: white; padding: 24px; border-radius: 8px 8px 0 0; text-align: center; }
        .body   { background: #f8fafc; padding: 32px; border: 1px solid #e2e8f0; }
        .footer { background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
        .box    { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .severity-high   { color: #dc2626; font-weight: bold; }
        .severity-medium { color: #d97706; font-weight: bold; }
        .severity-low    { color: #16a34a; }
        .label  { color: #64748b; font-size: 13px; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>⚠️ Rapport d'Incident</h2>
    </div>
    <div class="body">
        <p>Bonjour <strong>{{ $parent->full_name }}</strong>,</p>
        <p>Nous vous informons d'un incident concernant votre enfant <strong>{{ $incident->student?->full_name }}</strong>.</p>

        <div class="box">
            <div class="label">Date de l'incident</div>
            <p>{{ $incident->incident_date->format('d/m/Y') }}</p>

            <div class="label">Type</div>
            <p>{{ $incident->title }}</p>

            <div class="label">Sévérité</div>
            <p class="severity-{{ $incident->severity }}">
                {{ match($incident->severity) { 'high' => 'Élevée', 'medium' => 'Moyenne', default => 'Faible' } }}
            </p>

            <div class="label">Description</div>
            <p>{{ $incident->description }}</p>

            @if($incident->action_taken)
            <div class="label">Mesures prises</div>
            <p>{{ $incident->action_taken }}</p>
            @endif
        </div>

        <p>Nous restons disponibles pour tout renseignement complémentaire. N'hésitez pas à contacter l'administration.</p>
    </div>
    <div class="footer">
        <p>EduManage — Système de gestion scolaire</p>
    </div>
</body>
</html>
