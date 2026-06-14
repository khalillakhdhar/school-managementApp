<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1d4ed8; color: white; padding: 24px; border-radius: 8px 8px 0 0; text-align: center; }
        .body   { background: #f8fafc; padding: 32px; border: 1px solid #e2e8f0; }
        .footer { background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
        .box    { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .credential { font-family: monospace; background: #f1f5f9; padding: 8px 16px; border-radius: 4px; font-size: 16px; }
        .btn    { display: inline-block; background: #1d4ed8; color: white; padding: 12px 28px; border-radius: 6px; text-decoration: none; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>EduManage — Portail Parents</h2>
    </div>
    <div class="body">
        <p>Bonjour <strong>{{ $parent->full_name }}</strong>,</p>
        <p>Votre compte d'accès au portail parents a été créé. Vous pouvez maintenant suivre la scolarité et les paiements de vos enfants.</p>

        <div class="box">
            <h3 style="margin-top:0;">Vos identifiants de connexion</h3>
            <p><strong>Email :</strong><br><span class="credential">{{ $parent->email }}</span></p>
            <p><strong>Mot de passe temporaire :</strong><br><span class="credential">{{ $temporaryPassword }}</span></p>
            <p style="color:#ef4444; font-size:13px;">⚠️ Vous devrez changer ce mot de passe à votre première connexion.</p>
        </div>

        <a href="{{ $loginUrl }}" class="btn">Accéder au portail</a>

        <p style="margin-top: 24px; font-size: 13px; color: #64748b;">
            Si vous n'êtes pas à l'origine de cette demande, veuillez contacter l'administration de l'école.
        </p>
    </div>
    <div class="footer">
        <p>EduManage — Système de gestion scolaire</p>
    </div>
</body>
</html>
