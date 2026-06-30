<?php

namespace Tests\Feature;

use App\Models\School;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 1 — School (tenant) model: branding contracts, status helpers, auto-slug.
 */
class SchoolModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_slug_est_genere_automatiquement_et_unique(): void
    {
        $a = School::create(['name' => 'École El Amana']);
        $b = School::create(['name' => 'École El Amana']); // même nom

        $this->assertSame('ecole-el-amana', $a->slug);
        $this->assertSame('ecole-el-amana-2', $b->slug);
    }

    public function test_le_slug_fourni_est_respecte(): void
    {
        $school = School::create(['name' => 'École X', 'slug' => 'mon-slug']);

        $this->assertSame('mon-slug', $school->slug);
    }

    public function test_les_contrats_de_branding_filament_renvoient_les_bonnes_valeurs(): void
    {
        $school = School::factory()->create([
            'name'          => 'École Test',
            'logo_path'     => 'schools/1/logo.png',
            'primary_color' => '#10B981',
        ]);

        $this->assertInstanceOf(HasName::class, $school);
        $this->assertInstanceOf(HasAvatar::class, $school);

        $this->assertSame('École Test', $school->getFilamentName());
        $this->assertStringContainsString('schools/1/logo.png', $school->getFilamentAvatarUrl());
        $this->assertSame('#10B981', $school->brandColor());
    }

    public function test_la_couleur_par_defaut_est_appliquee_sans_branding(): void
    {
        $school = School::factory()->create(['primary_color' => null]);

        $this->assertSame('#2563EB', $school->brandColor());
        $this->assertNull($school->getFilamentAvatarUrl());
    }

    public function test_les_helpers_de_statut_et_essai(): void
    {
        $active    = School::factory()->create();
        $suspended = School::factory()->suspended()->create();
        $expired   = School::factory()->trial()->create(['trial_ends_at' => now()->subDay()]);
        $running   = School::factory()->trial()->create(['trial_ends_at' => now()->addDays(10)]);

        $this->assertTrue($active->isActive());
        $this->assertTrue($suspended->isSuspended());
        $this->assertTrue($expired->isOnTrial());
        $this->assertTrue($expired->trialHasExpired());
        $this->assertFalse($running->trialHasExpired());
    }

    public function test_le_soft_delete_preserve_l_unicite_du_slug(): void
    {
        $a = School::create(['name' => 'École Z']);
        $a->delete(); // soft delete

        $b = School::create(['name' => 'École Z']);

        // Le slug du soft-deleted est toujours réservé → -2
        $this->assertSame('ecole-z-2', $b->slug);
    }
}
