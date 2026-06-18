<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\ReportCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErpCoreTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers de fixtures ────────────────────────────────────────────────

    private function makeTeacher(): array
    {
        $user = User::create([
            'name' => 'Prof Test', 'email' => 'prof@test.tn',
            'password' => bcrypt('secret123'), 'role' => 'teacher',
        ]);
        $emp = Employee::create([
            'user_id' => $user->id, 'first_name' => 'Prof', 'last_name' => 'Test',
            'position' => 'Enseignant', 'phone' => '+216 20 000 001', 'is_teacher' => true,
            'is_active' => true, 'contract_type' => 'permanent', 'salary_base' => 1500,
            'start_date' => '2024-09-01',
        ]);

        return [$user, $emp];
    }

    private function makeClassroom(?int $teacherId = null): Classroom
    {
        $level = Level::create(['name' => '1ère année', 'code' => '1AP', 'order' => 1]);

        return Classroom::create([
            'name' => '1A', 'level_id' => $level->id, 'teacher_id' => $teacherId, 'capacity' => 30,
        ]);
    }

    private function makeStudent(Classroom $class): Student
    {
        return Student::create([
            'first_name' => 'Eleve', 'last_name' => 'Test',
            'date_of_birth' => '2018-05-10', 'enrollment_date' => '2025-09-01',
            'class' => $class->name, 'level' => '1ère année',
            'status' => 'active', 'classroom_id' => $class->id,
        ]);
    }

    // ── Accès par rôle ─────────────────────────────────────────────────────

    public function test_admin_peut_acceder_au_panel_admin(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'a@test.tn', 'password' => bcrypt('x'), 'role' => 'admin']);
        $this->actingAs($admin)->get('/admin/students')->assertStatus(200);
    }

    public function test_enseignant_ne_peut_pas_acceder_au_panel_admin(): void
    {
        [$user] = $this->makeTeacher();
        $this->actingAs($user)->get('/admin/students')->assertStatus(403);
    }

    public function test_parent_peut_acceder_a_son_portail(): void
    {
        $user = User::create(['name' => 'P', 'email' => 'p@test.tn', 'password' => bcrypt('x'), 'role' => 'parent']);
        SchoolParent::create(['first_name' => 'Par', 'last_name' => 'Ent', 'phone' => '+216 20 000 002', 'email' => 'p@test.tn', 'user_id' => $user->id]);
        $this->actingAs($user)->get('/parent/parent-dashboard')->assertStatus(200);
    }

    public function test_premiere_connexion_force_le_changement_de_mot_de_passe(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'a2@test.tn', 'password' => bcrypt('x'),
            'role' => 'admin', 'must_change_password' => true,
        ]);
        $this->actingAs($admin)->get('/admin/students')->assertRedirect(route('password.change'));
    }

    // ── Bulletin (ReportCardService) ───────────────────────────────────────

    public function test_le_bulletin_calcule_la_moyenne_ponderee(): void
    {
        $class = $this->makeClassroom();
        $student = $this->makeStudent($class);
        $math = Subject::create(['name' => 'Maths', 'code' => 'M', 'coefficient' => 4, 'is_active' => true]);
        $arabe = Subject::create(['name' => 'Arabe', 'code' => 'A', 'coefficient' => 2, 'is_active' => true]);

        Grade::create(['student_id' => $student->id, 'subject_id' => $math->id, 'classroom_id' => $class->id, 'term' => 'T1', 'score' => 15, 'max_score' => 20, 'coefficient' => 4]);
        Grade::create(['student_id' => $student->id, 'subject_id' => $arabe->id, 'classroom_id' => $class->id, 'term' => 'T1', 'score' => 9, 'max_score' => 20, 'coefficient' => 2]);

        $report = ReportCardService::forStudent($student->fresh('classroom'), 'T1');

        // (15*4 + 9*2) / (4+2) = 78 / 6 = 13.00
        $this->assertEquals(13.0, $report['average']);
        $this->assertEquals(1, $report['rank']);
        $this->assertEquals('Bien', $report['mention']);
        $this->assertCount(2, $report['lines']);
    }

    // ── Vérification de paiement + audit ───────────────────────────────────

    public function test_la_validation_de_paiement_est_tracee_dans_l_audit(): void
    {
        $admin = User::create(['name' => 'Compta', 'email' => 'c@test.tn', 'password' => bcrypt('x'), 'role' => 'admin']);
        $this->actingAs($admin);

        $class = $this->makeClassroom();
        $student = $this->makeStudent($class);
        $payment = Payment::create([
            'student_id' => $student->id, 'amount' => 200, 'payment_date' => now(),
            'due_date' => now(), 'status' => 'paid', 'payment_method' => 'cash',
        ]);

        app(PaymentService::class)->verify($payment);

        $this->assertTrue($payment->fresh()->is_verified);
        $this->assertEquals($admin->id, $payment->fresh()->verified_by);

        // un log de création + un log de modification (validation)
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Payment::class,
            'auditable_id'   => $payment->id,
            'event'          => 'updated',
        ]);
    }

    // ── Paie tunisienne (CNSS) ─────────────────────────────────────────────

    public function test_la_cnss_salariale_est_proche_de_9_18_pourcent(): void
    {
        [$user, $emp] = $this->makeTeacher();
        $payroll = Payroll::create([
            'employee_id' => $emp->id, 'month' => 1, 'year' => 2026,
            'salary_base' => 1000, 'gross_salary' => 1000, 'net_salary' => 1000, 'status' => 'draft',
        ]);

        $cnss = (float) $payroll->calculateCNSS();
        // CNSS salarié ≈ 9,18% de l'assiette cotisable
        $this->assertGreaterThan(80, $cnss);
        $this->assertLessThan(100, $cnss);
    }
}
