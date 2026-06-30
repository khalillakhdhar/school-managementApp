<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Level;
use App\Models\School;
use App\Models\Service;
use App\Models\Subject;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 2 — verifies the BelongsToSchool trait rollout works across several
 * tenant-owned models (not just the Student pilot): read scope filters by the
 * current tenant, and the creating hook stamps school_id automatically.
 */
class TenantScopeRolloutTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_scope_lecture_filtre_plusieurs_modeles(): void
    {
        $a = School::create(['name' => 'A', 'slug' => 'a']);
        $b = School::create(['name' => 'B', 'slug' => 'b']);

        // Two rows per model, one per school (bypass scope for setup).
        Level::withoutGlobalScope('school')->create(['school_id' => $a->id, 'name' => 'N-A', 'code' => 'NA', 'order' => 1]);
        Level::withoutGlobalScope('school')->create(['school_id' => $b->id, 'name' => 'N-B', 'code' => 'NB', 'order' => 1]);
        Service::withoutGlobalScope('school')->create(['school_id' => $a->id, 'name' => 'S-A', 'type' => 'monthly', 'amount' => 10, 'is_active' => true]);
        Service::withoutGlobalScope('school')->create(['school_id' => $b->id, 'name' => 'S-B', 'type' => 'monthly', 'amount' => 10, 'is_active' => true]);
        Subject::withoutGlobalScope('school')->create(['school_id' => $a->id, 'name' => 'Sub-A', 'code' => 'A1', 'coefficient' => 1, 'is_active' => true]);
        Subject::withoutGlobalScope('school')->create(['school_id' => $b->id, 'name' => 'Sub-B', 'code' => 'B1', 'coefficient' => 1, 'is_active' => true]);

        // No tenant → everything visible.
        $this->assertSame(2, Level::count());
        $this->assertSame(2, Service::count());
        $this->assertSame(2, Subject::count());

        // Tenant A → only A's rows, across all models.
        Filament::setTenant($a, isQuiet: true);
        $this->assertSame(1, Level::count());
        $this->assertSame('N-A', Level::first()->name);
        $this->assertSame('S-A', Service::first()->name);
        $this->assertSame('Sub-A', Subject::first()->name);
    }

    public function test_le_scope_ecriture_estampille_plusieurs_modeles(): void
    {
        $a = School::create(['name' => 'A', 'slug' => 'a']);
        Filament::setTenant($a, isQuiet: true);

        $level = Level::create(['name' => 'CP', 'code' => 'CP', 'order' => 1]);
        $service = Service::create(['name' => 'Cantine', 'type' => 'monthly', 'amount' => 80, 'is_active' => true]);
        $classroom = Classroom::create(['name' => '1A', 'level_id' => $level->id, 'capacity' => 30]);

        $this->assertSame($a->id, $level->fresh()->school_id);
        $this->assertSame($a->id, $service->fresh()->school_id);
        $this->assertSame($a->id, $classroom->fresh()->school_id);
    }
}
