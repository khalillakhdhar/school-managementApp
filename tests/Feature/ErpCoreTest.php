<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\SchoolParent;
use App\Models\Service;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TimetableEntry;
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

    public function test_parent_ne_peut_pas_acceder_au_panel_admin(): void
    {
        $user = User::create(['name' => 'Parent', 'email' => 'parent-admin@test.tn', 'password' => bcrypt('x'), 'role' => 'parent']);
        SchoolParent::create(['first_name' => 'Par', 'last_name' => 'Ent', 'phone' => '+216 20 000 022', 'email' => 'parent-admin@test.tn', 'user_id' => $user->id]);

        $this->actingAs($user)->get('/admin/students')->assertStatus(403);
    }

    public function test_admin_peut_acceder_aux_ressources_critiques(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin-critical@test.tn', 'password' => bcrypt('x'), 'role' => 'admin']);

        $this->actingAs($admin)->get('/admin/students')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/payments')->assertStatus(200);
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

    public function test_pdf_bulletin_respecte_les_acces_parent_et_enseignant(): void
    {
        [$teacherUser, $teacher] = $this->makeTeacher();
        $class = $this->makeClassroom();
        $student = $this->makeStudent($class);
        $subject = Subject::create(['name' => 'Maths', 'code' => 'MATH', 'coefficient' => 4, 'is_active' => true]);
        TimetableEntry::create([
            'classroom_id' => $class->id,
            'subject_id' => $subject->id,
            'employee_id' => $teacher->id,
            'day_of_week' => 'Lundi',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $parentUser = User::create(['name' => 'Parent', 'email' => 'parent-pdf@test.tn', 'password' => bcrypt('x'), 'role' => 'parent']);
        $parent = SchoolParent::create(['first_name' => 'Par', 'last_name' => 'Ent', 'phone' => '+216 20 000 023', 'email' => 'parent-pdf@test.tn', 'user_id' => $parentUser->id]);
        $parent->students()->attach($student->id, ['relation' => 'father']);

        $otherParentUser = User::create(['name' => 'Autre Parent', 'email' => 'other-parent-pdf@test.tn', 'password' => bcrypt('x'), 'role' => 'parent']);
        SchoolParent::create(['first_name' => 'Autre', 'last_name' => 'Parent', 'phone' => '+216 20 000 024', 'email' => 'other-parent-pdf@test.tn', 'user_id' => $otherParentUser->id]);

        $otherTeacherUser = User::create(['name' => 'Autre Prof', 'email' => 'other-teacher-pdf@test.tn', 'password' => bcrypt('x'), 'role' => 'teacher']);
        Employee::create([
            'user_id' => $otherTeacherUser->id, 'first_name' => 'Autre', 'last_name' => 'Prof',
            'position' => 'Enseignant', 'phone' => '+216 20 000 025', 'is_teacher' => true,
            'is_active' => true, 'contract_type' => 'permanent', 'salary_base' => 1500,
            'start_date' => '2024-09-01',
        ]);

        $this->actingAs($parentUser)->get(route('pdf.bulletin', [$student, 'T1']))->assertOk();
        $this->actingAs($teacherUser)->get(route('pdf.bulletin', [$student, 'T1']))->assertOk();
        $this->actingAs($otherParentUser)->get(route('pdf.bulletin', [$student, 'T1']))->assertForbidden();
        $this->actingAs($otherTeacherUser)->get(route('pdf.bulletin', [$student, 'T1']))->assertForbidden();
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

    public function test_paiement_enregistre_le_montant_pivot_des_services(): void
    {
        $class = $this->makeClassroom();
        $student = $this->makeStudent($class);
        $service = Service::create(['name' => 'Transport', 'type' => 'monthly', 'amount' => 75.500, 'is_active' => true]);

        $payment = app(PaymentService::class)->recordPayment($student->id, 75.500, 'cash', [$service->id]);

        $this->assertDatabaseHas('payment_service', [
            'payment_id' => $payment->id,
            'service_id' => $service->id,
            'amount' => 75.500,
        ]);
    }

    public function test_solde_et_retard_de_paiement_sont_explicites(): void
    {
        $class = $this->makeClassroom();
        $student = $this->makeStudent($class);

        Payment::create([
            'student_id' => $student->id, 'amount' => 100, 'payment_date' => now()->subDays(3),
            'due_date' => now()->subDays(3), 'status' => 'pending', 'payment_method' => 'cash',
        ]);
        Payment::create([
            'student_id' => $student->id, 'amount' => 50, 'payment_date' => now(),
            'due_date' => now()->subDays(1), 'status' => 'paid', 'payment_method' => 'cash',
        ]);
        Payment::create([
            'student_id' => $student->id, 'amount' => 25, 'payment_date' => now()->addDays(5),
            'due_date' => now()->addDays(5), 'status' => 'pending', 'payment_method' => 'cash',
        ]);

        $balance = app(PaymentService::class)->getStudentBalance($student);

        $this->assertSame(125.0, $balance['pending_amount']);
        $this->assertSame(50.0, $balance['paid_amount']);
        $this->assertSame(100.0, $balance['overdue_amount']);
        $this->assertSame(1, $balance['overdue_count']);
        $this->assertTrue($balance['is_overdue']);
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

    // ── Notifications in-app ───────────────────────────────────────────────

    public function test_un_incident_notifie_les_admins_en_base(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'an@test.tn', 'password' => bcrypt('x'), 'role' => 'admin']);
        $class = $this->makeClassroom();
        $student = $this->makeStudent($class);

        \App\Models\Incident::create([
            'student_id' => $student->id, 'title' => 'Bagarre', 'description' => 'x',
            'type' => 'disciplinary', 'severity' => 'high', 'incident_date' => now(), 'parent_notified' => false,
        ]);

        // L'observer envoie une notification in-app (notifyNow → persistée immédiatement).
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id'   => $admin->id,
        ]);
    }

    public function test_fiche_de_paie_finalisee_notifie_l_employe(): void
    {
        [$user, $emp] = $this->makeTeacher();
        $payroll = Payroll::create([
            'employee_id' => $emp->id, 'month' => 1, 'year' => 2026,
            'salary_base' => 1000, 'gross_salary' => 1000, 'net_salary' => 900, 'status' => 'draft',
        ]);

        $payroll->update(['status' => 'finalized']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
        ]);
    }
}
