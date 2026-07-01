<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Level;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 6/7 — provisioning commands: a new school comes up fully scoped, and the
 * platform super-admin is created above tenants.
 */
class SchoolProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_create_provisions_a_scoped_tenant(): void
    {
        $this->artisan('school:create', [
            'name' => 'École Test',
            '--admin-email' => 'admin@ecole-test.tn',
            '--admin-name' => 'Directeur',
        ])->assertSuccessful();

        $school = School::where('name', 'École Test')->firstOrFail();
        $this->assertSame(School::STATUS_TRIAL, $school->status);
        $this->assertNotNull($school->trial_ends_at);

        // Admin exists, is a member, and must change password.
        $admin = User::where('email', 'admin@ecole-test.tn')->firstOrFail();
        $this->assertTrue($admin->must_change_password);
        $this->assertTrue($school->users()->whereKey($admin->id)->exists());

        // Settings + minimal seed are scoped to the new tenant.
        Tenancy::runFor($school, function () use ($school): void {
            $this->assertSame($school->id, SchoolSetting::getInstance()->school_id);
            $this->assertSame(6, Level::count());
            $this->assertGreaterThan(0, Holiday::count());
            $this->assertTrue(Level::get()->every(fn ($l) => $l->school_id === $school->id));
        });
    }

    public function test_two_schools_can_be_provisioned_without_unique_clashes(): void
    {
        $this->artisan('school:create', [
            'name' => 'A', '--admin-email' => 'a@x.tn', '--admin-name' => 'A',
        ])->assertSuccessful();

        // Same standard levels/holidays for the second school must not clash.
        $this->artisan('school:create', [
            'name' => 'B', '--admin-email' => 'b@x.tn', '--admin-name' => 'B',
        ])->assertSuccessful();

        $this->assertSame(12, Level::withoutGlobalScope('school')->count()); // 6 + 6
    }

    public function test_platform_admin_command_creates_super_admin(): void
    {
        $this->artisan('platform:create-admin', [
            '--email' => 'ops@elitecampus.tn', '--name' => 'Ops',
        ])->assertSuccessful();

        $user = User::where('email', 'ops@elitecampus.tn')->firstOrFail();
        $this->assertSame('platform_admin', $user->role);
        $this->assertTrue($user->isPlatformAdmin());
        // Above tenants: not attached to any school.
        $this->assertSame(0, $user->schools()->count());
    }
}
