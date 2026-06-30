<?php

namespace App\Console\Commands;

use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * PHASE 2/5 — idempotent backfill of the existing single-school data into
 * tenant #1. Safe to re-run: it only touches rows where school_id IS NULL,
 * uses insertOrIgnore for memberships, and resolves the school by a stable slug.
 *
 *   php artisan tenancy:backfill
 */
class TenancyBackfill extends Command
{
    protected $signature = 'tenancy:backfill {--slug=ecole-principale : Stable slug for the primary tenant}';
    protected $description = 'Backfill existing (single-school) data into tenant #1';

    /** All tenant-owned tables receiving school_id. */
    private array $tables = [
        'students', 'parents', 'employees', 'classrooms', 'levels', 'subjects',
        'payments', 'payrolls', 'expenses', 'expense_categories', 'incidents',
        'attendances', 'student_attendances', 'holidays', 'blog_posts',
        'services', 'timetable_entries', 'grades', 'audit_logs', 'school_settings',
    ];

    public function handle(): int
    {
        $slug = $this->option('slug');

        $school = $this->resolvePrimarySchool($slug);
        $this->info("Tenant #{$school->id} : {$school->name} (slug: {$school->slug})");

        // 1. Backfill every tenant table (only NULL rows).
        $report = [];
        DB::transaction(function () use ($school, &$report) {
            foreach ($this->tables as $table) {
                $updated = DB::table($table)->whereNull('school_id')->update(['school_id' => $school->id]);
                $report[$table] = $updated;
            }
        });

        // 2. Attach all admin users as members of the primary tenant.
        $adminIds = DB::table('users')->where('role', 'admin')->pluck('id');
        $attached = 0;
        foreach ($adminIds as $uid) {
            $inserted = DB::table('school_user')->insertOrIgnore([
                'school_id' => $school->id, 'user_id' => $uid,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $attached += $inserted;
        }

        // 3. Report + verification.
        $this->newLine();
        $this->line('<options=bold>Backfill par table (lignes mises à jour) :</>');
        foreach ($report as $table => $count) {
            $this->line(sprintf('  %-22s %d', $table, $count));
        }
        $this->newLine();
        $this->info("Admins rattachés au tenant : {$attached} (sur {$adminIds->count()}).");

        // 4. Integrity check — no NULL school_id should remain.
        $remaining = [];
        foreach ($this->tables as $table) {
            $nulls = DB::table($table)->whereNull('school_id')->count();
            if ($nulls > 0) {
                $remaining[$table] = $nulls;
            }
        }

        if ($remaining !== []) {
            $this->newLine();
            $this->error('Des lignes ont encore school_id = NULL :');
            foreach ($remaining as $table => $nulls) {
                $this->line("  {$table}: {$nulls}");
            }
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✔ Vérification OK — aucune ligne orpheline (school_id NULL).');

        return self::SUCCESS;
    }

    private function resolvePrimarySchool(string $slug): School
    {
        $existing = School::withTrashed()->where('slug', $slug)->first();
        if ($existing) {
            return $existing;
        }

        // Seed identity from the current singleton school_settings if present.
        $settings = DB::table('school_settings')->orderBy('id')->first();

        return School::create([
            'name'    => $settings->school_name ?? config('app.name', 'EliteCampus'),
            'slug'    => $slug,
            'status'  => School::STATUS_ACTIVE,
            'plan'    => 'standard',
            'email'   => $settings->email ?? null,
            'phone'   => $settings->phone ?? null,
            'city'    => $settings->city ?? null,
            'country' => $settings->country ?? 'Tunisie',
        ]);
    }
}
