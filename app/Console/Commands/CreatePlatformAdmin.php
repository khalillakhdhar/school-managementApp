<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * PHASE 7 — create the SaaS super-admin who manages the /platform panel.
 * This account lives above tenants and is NOT attached to any school.
 *
 *   php artisan platform:create-admin --email=ops@elitecampus.tn --name="Ops"
 */
class CreatePlatformAdmin extends Command
{
    protected $signature = 'platform:create-admin
        {--email= : Super-admin email}
        {--name= : Full name}
        {--password= : Optional password (random when omitted)}';

    protected $description = 'Create a platform super-admin (access to /platform, above all tenants)';

    public function handle(): int
    {
        $email = trim((string) $this->option('email'));
        $name  = trim((string) $this->option('name')) ?: 'Platform Admin';

        if ($email === '') {
            $this->error('--email est obligatoire.');
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("Un utilisateur existe déjà avec l'email {$email}.");
            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?: Str::password(14));

        User::create([
            'name'                 => $name,
            'email'                => $email,
            'password'             => Hash::make($password),
            'role'                 => 'platform_admin',
            'must_change_password' => true,
        ]);

        $this->newLine();
        $this->info("✔ Super-admin plateforme créé : {$email}");
        $this->line('  Panel : /platform/login');
        $this->line("  Mot de passe temporaire : <options=bold>{$password}</>  (à changer à la 1ère connexion)");
        $this->newLine();

        return self::SUCCESS;
    }
}
