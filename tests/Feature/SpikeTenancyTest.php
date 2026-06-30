<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Student;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 0 SPIKE — validates the core multi-tenant mechanism:
 * the BelongsToSchool global scope reads Filament::getTenant() and filters
 * reads, while the creating hook stamps school_id from the tenant.
 *
 * If this passes, the Filament v5 tenancy approach is GO for the full rollout.
 */
class SpikeTenancyTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(int $schoolId, string $first): Student
    {
        return Student::withoutGlobalScope('school')->create([
            'school_id' => $schoolId,
            'first_name' => $first, 'last_name' => 'Test',
            'date_of_birth' => '2018-05-10', 'enrollment_date' => '2025-09-01',
            'class' => '1A', 'level' => '1ère année', 'status' => 'active',
        ]);
    }

    public function test_le_scope_lecture_filtre_par_tenant_courant(): void
    {
        $schoolA = School::create(['name' => 'École A', 'slug' => 'ecole-a']);
        $schoolB = School::create(['name' => 'École B', 'slug' => 'ecole-b']);

        $this->makeStudent($schoolA->id, 'Alpha');
        $this->makeStudent($schoolA->id, 'Beta');
        $this->makeStudent($schoolB->id, 'Gamma');

        // Sans tenant : le scope est no-op (CLI/queue/super-admin) → tout est visible.
        $this->assertSame(3, Student::count());

        // Tenant = École A → seules les lignes de A.
        Filament::setTenant($schoolA, isQuiet: true);
        $this->assertSame(2, Student::count());
        $this->assertEqualsCanonicalizing(['Alpha', 'Beta'], Student::pluck('first_name')->all());

        // Tenant = École B → seule la ligne de B.
        Filament::setTenant($schoolB, isQuiet: true);
        $this->assertSame(1, Student::count());
        $this->assertSame('Gamma', Student::first()->first_name);
    }

    public function test_le_scope_ecriture_estampille_le_tenant_courant(): void
    {
        $schoolA = School::create(['name' => 'École A', 'slug' => 'ecole-a']);

        Filament::setTenant($schoolA, isQuiet: true);

        $student = Student::create([
            'first_name' => 'Delta', 'last_name' => 'Test',
            'date_of_birth' => '2018-05-10', 'enrollment_date' => '2025-09-01',
            'class' => '1A', 'level' => '1ère année', 'status' => 'active',
        ]);

        $this->assertSame($schoolA->id, $student->fresh()->school_id);
    }

    public function test_un_user_n_accede_qu_a_ses_propres_ecoles(): void
    {
        $schoolA = School::create(['name' => 'École A', 'slug' => 'ecole-a']);
        $schoolB = School::create(['name' => 'École B', 'slug' => 'ecole-b']);

        $user = \App\Models\User::create([
            'name' => 'Admin A', 'email' => 'admina@test.tn',
            'password' => bcrypt('x'), 'role' => 'admin',
        ]);
        $user->schools()->attach($schoolA->id);

        $this->assertTrue($user->canAccessTenant($schoolA));
        $this->assertFalse($user->canAccessTenant($schoolB));
        $this->assertCount(1, $user->getTenants(Filament::getPanel('spike')));
    }
}
