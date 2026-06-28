<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Holiday;
use App\Models\Level;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@test.tn', 'password' => bcrypt('x'), 'role' => 'admin']);
    }

    private function makeTeacher(): array
    {
        $user = User::create(['name' => 'Prof', 'email' => 'prof@test.tn', 'password' => bcrypt('x'), 'role' => 'teacher']);
        $emp = Employee::create([
            'user_id' => $user->id, 'first_name' => 'Prof', 'last_name' => 'Test',
            'position' => 'Enseignant', 'phone' => '+216 20 000 001', 'is_teacher' => true,
            'is_active' => true, 'contract_type' => 'permanent', 'salary_base' => 1500,
            'start_date' => '2024-09-01',
        ]);

        return [$user, $emp];
    }

    private function makeClassroomWithTeacher(?int $teacherId = null): Classroom
    {
        $level = Level::create(['name' => '1ère année', 'code' => '1AP', 'order' => 1]);

        return Classroom::create(['name' => '1A', 'level_id' => $level->id, 'teacher_id' => $teacherId, 'capacity' => 30]);
    }

    // ── Policies "admin only" ───────────────────────────────────────────────

    public function test_admin_only_policies_refusent_les_non_admins(): void
    {
        [$teacher] = $this->makeTeacher();

        $level = Level::create(['name' => '2ème année', 'code' => '2AP', 'order' => 2]);
        $service = Service::create(['name' => 'Transport', 'type' => 'monthly', 'amount' => 50, 'is_active' => true]);
        $category = ExpenseCategory::create(['name' => 'Fournitures']);
        $expense = Expense::create(['category_id' => $category->id, 'amount' => 100, 'date' => now(), 'description' => 'x']);
        $audit = AuditLog::create(['event' => 'created', 'auditable_type' => Level::class, 'auditable_id' => $level->id]);

        $this->assertTrue(Gate::forUser($this->makeAdmin())->allows('view', $level));
        $this->assertFalse(Gate::forUser($teacher)->allows('viewAny', Level::class));
        $this->assertFalse(Gate::forUser($teacher)->allows('view', $service));
        $this->assertFalse(Gate::forUser($teacher)->allows('view', $category));
        $this->assertFalse(Gate::forUser($teacher)->allows('view', $expense));
        $this->assertFalse(Gate::forUser($teacher)->allows('view', $audit));
        $this->assertFalse(Gate::forUser($teacher)->allows('create', AuditLog::class));
    }

    // ── BlogPost / Holiday : lecture publique, écriture admin ──────────────

    public function test_blog_et_jours_feries_sont_lisibles_par_tous_mais_modifiables_par_admin_seul(): void
    {
        [$teacher] = $this->makeTeacher();
        $post = BlogPost::create(['title' => 'Annonce', 'slug' => 'annonce', 'content' => 'x', 'is_published' => true]);
        $holiday = Holiday::create(['date' => '2026-03-20', 'name' => 'Indépendance', 'type' => 'national']);

        $this->assertTrue(Gate::forUser($teacher)->allows('view', $post));
        $this->assertTrue(Gate::forUser($teacher)->allows('viewAny', Holiday::class));
        $this->assertFalse(Gate::forUser($teacher)->allows('update', $post));
        $this->assertFalse(Gate::forUser($teacher)->allows('delete', $holiday));
        $this->assertTrue(Gate::forUser($this->makeAdmin())->allows('update', $post));
    }

    // ── Attendance (présence employé) : un employé ne voit que la sienne ───

    public function test_un_employe_ne_voit_que_sa_propre_presence(): void
    {
        [$user1, $emp1] = $this->makeTeacher();
        $user2 = User::create(['name' => 'Prof2', 'email' => 'prof2@test.tn', 'password' => bcrypt('x'), 'role' => 'teacher']);
        $emp2 = Employee::create([
            'user_id' => $user2->id, 'first_name' => 'Prof2', 'last_name' => 'Test',
            'position' => 'Enseignant', 'phone' => '+216 20 000 003', 'is_teacher' => true,
            'is_active' => true, 'contract_type' => 'permanent', 'salary_base' => 1500,
            'start_date' => '2024-09-01',
        ]);

        $att1 = Attendance::create(['employee_id' => $emp1->id, 'date' => now(), 'status' => 'present']);

        $this->assertTrue(Gate::forUser($user1)->allows('view', $att1));
        $this->assertFalse(Gate::forUser($user2)->allows('view', $att1));
    }

    // ── Classroom / Subject / TimetableEntry : un prof ne voit que les siens ─

    public function test_un_prof_ne_voit_que_ses_classes_et_son_emploi_du_temps(): void
    {
        [$user1, $emp1] = $this->makeTeacher();
        $user2 = User::create(['name' => 'Prof2', 'email' => 'prof2b@test.tn', 'password' => bcrypt('x'), 'role' => 'teacher']);
        Employee::create([
            'user_id' => $user2->id, 'first_name' => 'Prof2', 'last_name' => 'Test',
            'position' => 'Enseignant', 'phone' => '+216 20 000 004', 'is_teacher' => true,
            'is_active' => true, 'contract_type' => 'permanent', 'salary_base' => 1500,
            'start_date' => '2024-09-01',
        ]);

        $classroom = $this->makeClassroomWithTeacher();
        $subject = Subject::create(['name' => 'Maths', 'code' => 'M', 'coefficient' => 4, 'is_active' => true]);
        $entry = TimetableEntry::create([
            'classroom_id' => $classroom->id, 'subject_id' => $subject->id, 'employee_id' => $emp1->id,
            'day_of_week' => 'Lundi', 'start_time' => '08:00', 'end_time' => '09:00',
        ]);

        $this->assertTrue(Gate::forUser($user1)->allows('view', $classroom));
        $this->assertTrue(Gate::forUser($user1)->allows('view', $subject));
        $this->assertTrue(Gate::forUser($user1)->allows('view', $entry));

        $this->assertFalse(Gate::forUser($user2)->allows('view', $classroom));
        $this->assertFalse(Gate::forUser($user2)->allows('view', $subject));
        $this->assertFalse(Gate::forUser($user2)->allows('view', $entry));
    }

    // ── StudentAttendance : parent ne voit que ses enfants ─────────────────

    public function test_un_parent_ne_voit_que_la_presence_de_ses_propres_enfants(): void
    {
        [$teacher, $emp] = $this->makeTeacher();
        $classroom = $this->makeClassroomWithTeacher($emp->id);
        $subject = Subject::create(['name' => 'Maths', 'code' => 'M', 'coefficient' => 4, 'is_active' => true]);
        TimetableEntry::create([
            'classroom_id' => $classroom->id, 'subject_id' => $subject->id, 'employee_id' => $emp->id,
            'day_of_week' => 'Lundi', 'start_time' => '08:00', 'end_time' => '09:00',
        ]);

        $student = Student::create([
            'first_name' => 'Eleve', 'last_name' => 'Un',
            'date_of_birth' => '2018-05-10', 'enrollment_date' => '2025-09-01',
            'class' => $classroom->name, 'level' => '1ère année',
            'status' => 'active', 'classroom_id' => $classroom->id,
        ]);

        $parentUser = User::create(['name' => 'Parent', 'email' => 'parent@test.tn', 'password' => bcrypt('x'), 'role' => 'parent']);
        $parent = \App\Models\SchoolParent::create([
            'first_name' => 'Par', 'last_name' => 'Ent', 'phone' => '+216 20 000 005',
            'email' => 'parent@test.tn', 'user_id' => $parentUser->id,
        ]);
        $parent->students()->attach($student->id, ['relation' => 'father']);

        $otherParentUser = User::create(['name' => 'Autre', 'email' => 'autre@test.tn', 'password' => bcrypt('x'), 'role' => 'parent']);

        $att = StudentAttendance::create([
            'student_id' => $student->id, 'classroom_id' => $classroom->id, 'employee_id' => $emp->id,
            'date' => now(), 'status' => 'present',
        ]);

        $this->assertTrue(Gate::forUser($parentUser)->allows('view', $att));
        $this->assertFalse(Gate::forUser($otherParentUser)->allows('view', $att));
        $this->assertTrue(Gate::forUser($teacher)->allows('view', $att));
    }

    // ── Gate::before admin bypass ────────────────────────────────────────────

    public function test_admin_passe_outre_toutes_les_policies(): void
    {
        $admin = $this->makeAdmin();
        $level = Level::create(['name' => 'X', 'code' => 'X', 'order' => 9]);

        $this->assertTrue(Gate::forUser($admin)->allows('delete', $level));
        $this->assertTrue(Gate::forUser($admin)->allows('create', AuditLog::class));
    }
}
