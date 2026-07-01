<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 7 — the /platform super-admin panel: access control and that a
 * platform_admin stays on /platform (never bounced to /admin).
 */
class PlatformPanelTest extends TestCase
{
    use RefreshDatabase;

    private function platformAdmin(bool $mustChange = false): User
    {
        return User::create([
            'name' => 'Ops', 'email' => 'ops@test.tn',
            'password' => bcrypt('x'), 'role' => 'platform_admin',
            'must_change_password' => $mustChange,
        ]);
    }

    public function test_platform_admin_reaches_the_platform_dashboard(): void
    {
        $this->actingAs($this->platformAdmin())->get('/platform')->assertOk();
    }

    public function test_platform_admin_can_list_schools_and_users(): void
    {
        School::create(['name' => 'A', 'slug' => 'a']);
        $admin = $this->platformAdmin();

        $this->actingAs($admin)->get('/platform/schools')->assertOk();
        $this->actingAs($admin)->get('/platform/users')->assertOk();
    }

    public function test_platform_admin_cannot_enter_a_school_admin_panel(): void
    {
        // Not a member of any tenant + role isn't admin → refused.
        $status = $this->actingAs($this->platformAdmin())->get('/admin')->getStatusCode();
        $this->assertContains($status, [403, 404]);
    }

    public function test_forced_password_change_sends_platform_admin_back_to_platform(): void
    {
        $admin = $this->platformAdmin(mustChange: true);

        $this->actingAs($admin)->post('/account/password', [
            'current_password'      => 'x',
            'password'              => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ])->assertRedirect('/platform');

        $this->assertFalse($admin->fresh()->must_change_password);
    }
}
