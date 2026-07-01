<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolParent;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\User;
use App\Support\Tenancy;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 9 — BLOCKING before production. Proves one school never reads or writes
 * another school's data, across the scope layer, HTTP layer and aggregations.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: School, 1: User} */
    private function makeSchool(string $slug): array
    {
        $school = School::create(['name' => strtoupper($slug), 'slug' => $slug]);
        $admin = User::create([
            'name' => "Admin {$slug}", 'email' => "admin-{$slug}@test.tn",
            'password' => bcrypt('x'), 'role' => 'admin',
        ]);
        $school->users()->attach($admin->id);

        return [$school, $admin];
    }

    private function seedStudents(School $school, int $count): void
    {
        Tenancy::runFor($school, function () use ($count): void {
            for ($i = 1; $i <= $count; $i++) {
                Student::create([
                    'first_name' => "E{$i}", 'last_name' => 'X',
                    'date_of_birth' => '2018-01-01', 'enrollment_date' => '2025-09-01',
                    'class' => '1A', 'level' => '1', 'status' => 'active',
                ]);
            }
        });
    }

    public function test_read_scope_isolates_models_between_schools(): void
    {
        [$a] = $this->makeSchool('a');
        [$b] = $this->makeSchool('b');
        $this->seedStudents($a, 3);
        $this->seedStudents($b, 2);

        Filament::setTenant($a, isQuiet: true);
        $this->assertSame(3, Student::count());
        $this->assertTrue(Student::get()->every(fn (Student $s) => $s->school_id === $a->id));

        Filament::setTenant($b, isQuiet: true);
        $this->assertSame(2, Student::count());
        $this->assertTrue(Student::get()->every(fn (Student $s) => $s->school_id === $b->id));
    }

    public function test_creation_is_stamped_with_the_active_tenant(): void
    {
        [$a] = $this->makeSchool('a');
        Filament::setTenant($a, isQuiet: true);

        $student = Student::create([
            'first_name' => 'New', 'last_name' => 'One',
            'date_of_birth' => '2018-01-01', 'enrollment_date' => '2025-09-01',
            'class' => '1A', 'level' => '1', 'status' => 'active',
        ]);

        $this->assertSame($a->id, $student->fresh()->school_id);
    }

    public function test_admin_cannot_open_another_schools_panel(): void
    {
        [$a, $adminA] = $this->makeSchool('a');
        [$b] = $this->makeSchool('b');

        // Admin A is not a member of B → Filament refuses the tenant (403/404,
        // it uses 404 to avoid leaking the tenant's existence).
        $this->assertContains(
            $this->actingAs($adminA)->get("/admin/{$b->slug}/students")->getStatusCode(),
            [403, 404],
        );
        // On his own tenant it works.
        $this->actingAs($adminA)->get("/admin/{$a->slug}/students")->assertOk();
    }

    public function test_direct_access_to_a_foreign_record_is_not_found(): void
    {
        [$a, $adminA] = $this->makeSchool('a');
        [$b] = $this->makeSchool('b');

        $studentB = null;
        Tenancy::runFor($b, function () use (&$studentB): void {
            $studentB = Student::create([
                'first_name' => 'Foreign', 'last_name' => 'B',
                'date_of_birth' => '2018-01-01', 'enrollment_date' => '2025-09-01',
                'class' => '1A', 'level' => '1', 'status' => 'active',
            ]);
        });

        // Admin A tries to edit B's student through his own tenant URL → 404.
        $this->actingAs($adminA)
            ->get("/admin/{$a->slug}/students/{$studentB->id}/edit")
            ->assertNotFound();
    }

    public function test_aggregations_count_only_the_active_tenant(): void
    {
        [$a] = $this->makeSchool('a');
        [$b] = $this->makeSchool('b');
        $this->seedStudents($a, 4);
        $this->seedStudents($b, 1);

        $this->assertSame(4, Tenancy::runFor($a, fn () => Student::where('status', 'active')->count()));
        $this->assertSame(1, Tenancy::runFor($b, fn () => Student::where('status', 'active')->count()));

        // Platform-wide count (no scope) sees everyone.
        $this->assertSame(5, Student::withoutGlobalScope('school')->count());
    }

    public function test_same_level_code_is_allowed_in_two_schools(): void
    {
        [$a] = $this->makeSchool('a');
        [$b] = $this->makeSchool('b');

        Tenancy::runFor($a, fn () => Level::create(['name' => '1ère', 'code' => '1AP', 'order' => 1]));
        Tenancy::runFor($b, fn () => Level::create(['name' => '1ère', 'code' => '1AP', 'order' => 1]));

        // No unique-constraint violation: each school owns its own "1AP".
        $this->assertSame(2, Level::withoutGlobalScope('school')->where('code', '1AP')->count());
    }

    public function test_school_settings_are_per_tenant(): void
    {
        [$a] = $this->makeSchool('a');
        [$b] = $this->makeSchool('b');

        Tenancy::runFor($a, fn () => tap(SchoolSetting::getInstance(), fn ($s) => $s->update(['school_name' => 'Alpha'])));
        Tenancy::runFor($b, fn () => tap(SchoolSetting::getInstance(), fn ($s) => $s->update(['school_name' => 'Bravo'])));

        $this->assertSame('Alpha', Tenancy::runFor($a, fn () => SchoolSetting::get('school_name')));
        $this->assertSame('Bravo', Tenancy::runFor($b, fn () => SchoolSetting::get('school_name')));
    }

    public function test_payment_reminders_are_isolated_per_school(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        foreach (['a', 'b'] as $slug) {
            [$school] = $this->makeSchool($slug);
            Tenancy::runFor($school, function () use ($slug): void {
                $student = Student::create([
                    'first_name' => "S{$slug}", 'last_name' => 'X',
                    'date_of_birth' => '2018-01-01', 'enrollment_date' => '2025-09-01',
                    'class' => '1A', 'level' => '1', 'status' => 'active',
                ]);
                $parent = SchoolParent::create([
                    'first_name' => 'P', 'last_name' => 'X',
                    'phone' => '+216 20 000 00' . ($slug === 'a' ? '1' : '2'),
                    'email' => "parent-{$slug}@test.tn",
                ]);
                $parent->students()->attach($student->id, ['relation' => 'father']);
                Payment::create([
                    'student_id' => $student->id, 'amount' => 100,
                    'payment_date' => now()->subDays(40), 'due_date' => now()->subDays(10),
                    'status' => 'pending', 'payment_method' => 'cash',
                ]);
            });
        }

        $this->artisan('payments:send-reminders')->assertSuccessful();

        // One reminder per school's own overdue parent — two total, not cross-mixed.
        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\PaymentReminderMail::class, 2);
    }

    public function test_platform_admin_sees_every_school_but_admin_sees_one(): void
    {
        [$a, $adminA] = $this->makeSchool('a');
        $this->makeSchool('b');

        $platform = User::create([
            'name' => 'Super', 'email' => 'super@test.tn',
            'password' => bcrypt('x'), 'role' => 'platform_admin',
        ]);

        // getTenants drives the tenant switcher: admin A → 1, platform_admin → none (above tenants).
        $panel = Filament::getPanel('admin');
        $this->assertCount(1, $adminA->getTenants($panel));

        // School is not tenant-scoped: the platform operator counts all of them.
        $this->assertSame(2, School::count());
    }
}
