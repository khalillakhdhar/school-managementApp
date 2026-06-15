<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Changer mon mot de passe — EliteCampus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{font-family:'Inter',-apple-system,system-ui,sans-serif;box-sizing:border-box;margin:0;padding:0}
        body{background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{background:#fff;border-radius:18px;box-shadow:0 20px 60px rgba(0,0,0,.3);width:100%;max-width:420px;padding:36px 34px}
        .badge{width:54px;height:54px;border-radius:14px;background:linear-gradient(135deg,#2563eb,#1d4ed8);display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:18px}
        h1{font-size:21px;font-weight:800;color:#0f172a;letter-spacing:-.4px}
        .sub{font-size:13.5px;color:#64748b;margin-top:6px;line-height:1.5;margin-bottom:22px}
        label{display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:6px;margin-top:16px}
        input{width:100%;border:1px solid #dde3ea;border-radius:9px;padding:11px 13px;font-size:14px;color:#0f172a;outline:none;transition:border .15s,box-shadow .15s}
        input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
        button{width:100%;margin-top:24px;background:#2563eb;color:#fff;border:none;border-radius:10px;padding:13px;font-size:14.5px;font-weight:700;cursor:pointer;transition:background .15s}
        button:hover{background:#1d4ed8}
        .err{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-size:12.5px;border-radius:9px;padding:10px 13px;margin-bottom:6px}
        .hint{font-size:11.5px;color:#94a3b8;margin-top:6px}
        .logout{display:block;text-align:center;margin-top:18px;font-size:12.5px;color:#94a3b8;text-decoration:none}
        .logout:hover{color:#64748b}
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">🔒</div>
        <h1>Changer votre mot de passe</h1>
        <p class="sub">Pour votre sécurité, vous devez définir un nouveau mot de passe avant d'accéder à votre espace.</p>

        @if($errors->any())
            <div class="err">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf
            <label for="current_password">Mot de passe actuel</label>
            <input id="current_password" type="password" name="current_password" required autofocus>

            <label for="password">Nouveau mot de passe</label>
            <input id="password" type="password" name="password" required>
            <div class="hint">Au moins 8 caractères.</div>

            <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>

            <button type="submit">Mettre à jour et continuer</button>
        </form>

        <form method="POST" action="{{ $logoutUrl }}">
            @csrf
            <button type="submit" class="logout" style="background:none;color:#94a3b8;font-weight:500;margin-top:14px;padding:6px;">Se déconnecter</button>
        </form>
    </div>
</body>
</html>
