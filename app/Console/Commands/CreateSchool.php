<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\User;
use App\Services\HolidayService;
use App\Support\Tenancy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * PHASE 6 — provision a brand-new client school (tenant) end to end:
 * the school row, its admin account + membership, default settings, and a
 * minimal scoped seed (school levels + Tunisian public holidays).
 *
 *   php artisan school:create "Nom École" --admin-email=a@ex.tn --admin-name="Admin"
 */
class CreateSchool extends Command
{
    protected $signature = 'school:create
        {name : The school display name}
        {--admin-email= : Email of the school administrator account}
        {--admin-name= : Full name of the administrator}
        {--slug= : Optional URL slug (auto-generated when omitted)}
        {--plan=trial : Subscription plan}
        {--password= : Optional admin password (random when omitted)}';

    protected $description = 'Provision a new school (tenant) with an admin, settings and a minimal scoped seed';

    public function handle(): int
    {
        $name       = trim((string) $this->argument('name'));
        $adminEmail = trim((string) $this->option('admin-email'));
        $adminName  = trim((string) $this->option('admin-name')) ?: 'Administrateur';

        if ($name === '' || $adminEmail === '') {
            $this->error('Le nom de l\'école et --admin-email sont obligatoires.');
            return self::FAILURE;
        }

        if (User::where('email', $adminEmail)->exists()) {
            $this->error("Un utilisateur existe déjà avec l'email {$adminEmail}.");
            return self::FAILURE;
        }

        $plan     = (string) $this->option('plan');
        $isTrial  = $plan === 'trial';
        $password = (string) ($this->option('password') ?: Str::password(12));

        $school = null;

        DB::transaction(function () use ($name, $adminEmail, $adminName, $plan, $isTrial, $password, &$school) {
            $school = School::create([
                'name'          => $name,
                'slug'          => $this->option('slug') ?: null,
                'status'        => $isTrial ? School::STATUS_TRIAL : School::STATUS_ACTIVE,
                'plan'          => $plan,
                'trial_ends_at' => $isTrial ? now()->addDays(30) : null,
                'country'       => 'Tunisie',
            ]);

            $admin = User::create([
                'name'                 => $adminName,
                'email'                => $adminEmail,
                'password'             => Hash::make($password),
                'role'                 => 'admin',
                'must_change_password' => true,
            ]);
            $school->users()->attach($admin->id);

            // Settings row + minimal scoped seed, all inside the new tenant context.
            Tenancy::runFor($school, function () use ($school): void {
                SchoolSetting::create([
                    'school_id'     => $school->id,
                    'school_name'   => $school->name,
                    'country'       => 'Tunisie',
                    'academic_year' => now()->month >= 9
                        ? now()->year . '-' . (now()->year + 1)
                        : (now()->year - 1) . '-' . now()->year,
                ]);

                $this->seedLevels();
                HolidayService::sync((int) now()->year);
            });
        });

        $this->newLine();
        $this->info("✔ École créée : {$school->name} (#{$school->id}, slug: {$school->slug})");
        $this->line("  Panel   : /admin/{$school->slug}");
        $this->line("  Admin   : {$adminEmail}");
        $this->line("  Mot de passe temporaire : <options=bold>{$password}</>  (à changer à la 1ère connexion)");
        $this->newLine();

        return self::SUCCESS;
    }

    /** Standard Tunisian primary levels — created scoped to the current tenant. */
    private function seedLevels(): void
    {
        $defs = [
            ['1ère année', '1AP', 1], ['2ème année', '2AP', 2], ['3ème année', '3AP', 3],
            ['4ème année', '4AP', 4], ['5ème année', '5AP', 5], ['6ème année', '6AP', 6],
        ];

        foreach ($defs as [$label, $code, $order]) {
            \App\Models\Level::create([
                'name'        => $label,
                'code'        => $code,
                'order'       => $order,
                'description' => 'Niveau ' . $label,
            ]);
        }
    }
}
