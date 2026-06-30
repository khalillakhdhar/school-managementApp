<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * PHASE 3 — CLI/queue tenant context (App\Support\Tenancy) and per-school
 * isolation of the scheduled payment-reminders command.
 */
class TenancyContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_run_for_active_et_restaure_le_contexte(): void
    {
        $a = School::create(['name' => 'A', 'slug' => 'a']);

        $this->assertNull(Tenancy::current());            // pas de tenant au départ
        $this->assertFalse(Tenancy::check());

        $inside = Tenancy::runFor($a, fn () => Tenancy::id());

        $this->assertSame($a->id, $inside);               // contexte actif dans le callback
        $this->assertNull(Tenancy::current());            // restauré après
    }

    public function test_each_school_ne_parcourt_que_les_ecoles_vivantes(): void
    {
        $active    = School::create(['name' => 'Active', 'slug' => 'active', 'status' => School::STATUS_ACTIVE]);
        $trial     = School::create(['name' => 'Trial', 'slug' => 'trial', 'status' => School::STATUS_TRIAL]);
        $suspended = School::create(['name' => 'Susp', 'slug' => 'susp', 'status' => School::STATUS_SUSPENDED]);

        $seen = [];
        Tenancy::eachSchool(function (School $s) use (&$seen) {
            $seen[] = $s->id;
        });

        $this->assertEqualsCanonicalizing([$active->id, $trial->id], $seen);
        $this->assertNotContains($suspended->id, $seen);
    }

    public function test_les_rappels_sont_isoles_par_ecole(): void
    {
        Mail::fake();

        [$schoolA, $adminA] = $this->makeSchoolWithOverduePayment('a', 'Alpha');
        [$schoolB, $adminB] = $this->makeSchoolWithOverduePayment('b', 'Bravo');

        $this->artisan('payments:send-reminders')->assertSuccessful();

        // Chaque admin reçoit la synthèse de SON école uniquement (1 impayé chacun).
        $this->assertDatabaseHas('notifications', ['notifiable_type' => User::class, 'notifiable_id' => $adminA->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_type' => User::class, 'notifiable_id' => $adminB->id]);

        $notifA = \DB::table('notifications')->where('notifiable_id', $adminA->id)->first();
        $this->assertStringContainsString('1', $notifA->data); // 1 paiement en retard, pas 2

        // 2 emails au total (1 parent par école). PaymentReminderMail est
        // ShouldQueue → Mail::fake l'enregistre comme "queued", pas "sent".
        Mail::assertQueued(\App\Mail\PaymentReminderMail::class, 2);
    }

    /** @return array{0: School, 1: User} */
    private function makeSchoolWithOverduePayment(string $slug, string $studentName): array
    {
        $school = School::create(['name' => strtoupper($slug), 'slug' => $slug]);

        $admin = User::create([
            'name' => "Admin {$slug}", 'email' => "admin-{$slug}@test.tn",
            'password' => bcrypt('x'), 'role' => 'admin',
        ]);
        $school->users()->attach($admin->id);

        Tenancy::runFor($school, function () use ($studentName, $slug) {
            $student = Student::create([
                'first_name' => $studentName, 'last_name' => 'Test',
                'date_of_birth' => '2018-05-10', 'enrollment_date' => '2025-09-01',
                'class' => '1A', 'level' => '1ère', 'status' => 'active',
            ]);

            $parent = SchoolParent::create([
                'first_name' => 'Par', 'last_name' => 'Ent',
                'phone' => '+216 20 000 001', 'email' => "parent-{$slug}@test.tn",
            ]);
            $parent->students()->attach($student->id, ['relation' => 'father']);

            Payment::create([
                'student_id' => $student->id, 'amount' => 200,
                'payment_date' => now()->subDays(40), 'due_date' => now()->subDays(10),
                'status' => 'pending', 'payment_method' => 'cash',
            ]);
        });

        return [$school, $admin];
    }
}
