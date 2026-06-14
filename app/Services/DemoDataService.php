<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BlogPost;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Incident;
use App\Models\Level;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\SchoolParent;
use App\Models\SchoolSetting;
use App\Models\Service;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One-click demo data for a realistic Tunisian primary school.
 * seed()  -> populates the whole workflow (deterministic timetable).
 * purge() -> removes ALL demo/business data, keeps users + settings.
 */
class DemoDataService
{
    public const ACADEMIC_YEAR = '2025-2026';

    /** Shared password for every demo login account (teacher & parent). */
    public const DEMO_PASSWORD = 'demo1234';

    /** Business tables wiped on purge (FK-safe order). */
    protected static array $tables = [
        'payment_service', 'payments', 'service_student', 'services',
        'timetable_entries', 'employee_subject', 'classroom_subject',
        'attendances', 'payrolls', 'incidents',
        'parent_student', 'parents',
        'students', 'classrooms', 'subjects', 'levels',
        'expenses', 'expense_categories', 'blog_posts',
    ];

    public static function isActive(): bool
    {
        try {
            return (bool) SchoolSetting::getInstance()->demo_mode;
        } catch (\Throwable) {
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  SEED
    // ══════════════════════════════════════════════════════════════════════
    public static function seed(): array
    {
        // No DB::transaction wrapper: wipe() uses TRUNCATE which implicitly
        // commits on MySQL and would break an enclosing transaction.
        self::wipe();

        $settings = SchoolSetting::getInstance();
            $settings->fill([
                'school_name'   => 'École Privée El Amana',
                'slogan'        => 'Savoir, Respect, Excellence',
                'address'       => 'Avenue Habib Bourguiba',
                'city'          => 'Tunis',
                'country'       => 'Tunisie',
                'phone'         => '+216 71 245 678',
                'mobile'        => '+216 98 123 456',
                'email'         => 'contact@elamana.tn',
                'academic_year' => self::ACADEMIC_YEAR,
                'school_type'   => 'École primaire privée',
                'demo_mode'     => true,
            ])->save();

            $levels    = self::seedLevels();
            $subjects  = self::seedSubjects();
            $employees = self::seedEmployees($subjects);
            $teachers  = $employees->where('is_teacher', true)->values();
            $classes   = self::seedClassrooms($levels, $teachers, $subjects);
            $students  = self::seedStudents($classes);
            $parents   = self::seedParents($students);
            self::seedAccounts($employees, $parents);
            $services  = self::seedServices();
            self::seedServiceSubscriptions($students, $services);
            self::seedPayments($students, $services);
            self::seedTimetable($classes, $subjects, $teachers);
            self::seedAttendance($employees);
            self::seedPayrolls($teachers);
            $cats = self::seedExpenseCategories();
            self::seedExpenses($cats);
            self::seedIncidents($students);
            self::seedBlog();

            return [
                'levels'    => $levels->count(),
                'subjects'  => $subjects->count(),
                'employees' => $employees->count(),
                'classes'   => $classes->count(),
            'students'  => $students->count(),
            'payments'  => Payment::count(),
            'seances'   => TimetableEntry::count(),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PURGE
    // ══════════════════════════════════════════════════════════════════════
    public static function purge(): void
    {
        self::wipe();
        $settings = SchoolSetting::getInstance();
        $settings->demo_mode = false;
        $settings->save();
    }

    protected static function wipe(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
        foreach (self::$tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        // Remove demo login accounts (parents/teachers/staff); keep admins.
        User::where('role', '!=', 'admin')->delete();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    // ── Levels ────────────────────────────────────────────────────────────
    protected static function seedLevels()
    {
        $defs = [
            ['1ère année', '1AP', 1], ['2ème année', '2AP', 2], ['3ème année', '3AP', 3],
            ['4ème année', '4AP', 4], ['5ème année', '5AP', 5], ['6ème année', '6AP', 6],
        ];
        return collect($defs)->map(fn ($d) => Level::create([
            'name' => $d[0], 'code' => $d[1], 'order' => $d[2],
            'description' => 'Niveau ' . $d[0] . ' — enseignement de base',
        ]));
    }

    // ── Subjects ──────────────────────────────────────────────────────────
    protected static function seedSubjects()
    {
        $defs = [
            ['Arabe', 'AR', 4, '#ef4444'], ['Français', 'FR', 3, '#2563eb'],
            ['Mathématiques', 'MATH', 4, '#8b5cf6'], ['Éveil scientifique', 'EVS', 2, '#10b981'],
            ['Éducation islamique', 'EI', 1, '#0ea5e9'], ['Éducation civique', 'EC', 1, '#f59e0b'],
            ['Anglais', 'EN', 2, '#ec4899'], ['Éducation physique', 'EPS', 1, '#14b8a6'],
            ['Informatique', 'INFO', 1, '#6366f1'], ['Éducation artistique', 'ART', 1, '#f97316'],
        ];
        return collect($defs)->map(fn ($d) => Subject::create([
            'name' => $d[0], 'code' => $d[1], 'coefficient' => $d[2],
            'color' => $d[3], 'is_active' => true,
            'description' => 'Matière : ' . $d[0],
        ]));
    }

    // ── Employees (teachers + staff) ───────────────────────────────────────
    protected static function seedEmployees($subjects)
    {
        $teachers = [
            ['Salim', 'Whichi', 'Arabe'], ['Hanene', 'Trabelsi', 'Français'],
            ['Mohamed', 'Ben Salah', 'Mathématiques'], ['Ines', 'Gharbi', 'Éveil scientifique'],
            ['Karim', 'Jelassi', 'Éducation islamique'], ['Olfa', 'Bouazizi', 'Anglais'],
            ['Nizar', 'Khelifi', 'Éducation physique'], ['Sonia', 'Mansour', 'Informatique'],
            ['Walid', 'Hamdi', 'Mathématiques'], ['Rim', 'Chaabane', 'Français'],
            ['Anis', 'Tlili', 'Arabe'], ['Sana', 'Belhadj', 'Éducation artistique'],
        ];
        $staff = [
            ['Fethi', 'Maaloul', 'Directeur', 3200],
            ['Leila', 'Karoui', 'Secrétaire de direction', 1400],
            ['Hatem', 'Sassi', 'Surveillant général', 1600],
        ];

        $out = collect();
        $i = 0;
        foreach ($teachers as $t) {
            $i++;
            $emp = Employee::create([
                'first_name' => $t[0], 'last_name' => $t[1],
                'position' => 'Enseignant(e)', 'is_teacher' => true, 'is_active' => true,
                'phone' => '+216 ' . rand(20, 29) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                'email' => Str::slug($t[0] . '.' . $t[1]) . '@elamana.tn',
                'address' => 'Tunis',
                'salary_base' => rand(1300, 2200),
                'hourly_rate' => rand(18, 30),
                'contract_type' => $i <= 9 ? 'permanent' : 'contract',
                'start_date' => Carbon::create(rand(2015, 2023), rand(1, 9), 1),
                'cin' => (string) rand(10000000, 14999999),
                'matricule_cnss' => (string) rand(1000000, 9999999),
                'rib' => '08 ' . rand(100, 999) . ' ' . rand(1000000000000, 9999999999999),
                'specialite' => $t[2],
                'situation_familiale' => ['celibataire', 'marie', 'marie', 'divorce'][array_rand([0, 1, 2, 3])],
                'nb_enfants' => rand(0, 3),
                'indemnite_transport' => 60,
                'indemnite_logement' => rand(0, 150),
                'autres_indemnites' => 0,
            ]);
            // link teacher to their subject
            if ($subject = $subjects->firstWhere('name', $t[2])) {
                $emp->subjects()->attach($subject->id, [
                    'specialization' => $t[2], 'max_hours_per_week' => 24,
                ]);
            }
            $out->push($emp);
        }
        foreach ($staff as $s) {
            $out->push(Employee::create([
                'first_name' => $s[0], 'last_name' => $s[1],
                'position' => $s[2], 'is_teacher' => false, 'is_active' => true,
                'phone' => '+216 ' . rand(20, 29) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                'email' => Str::slug($s[0] . '.' . $s[1]) . '@elamana.tn',
                'address' => 'Tunis',
                'salary_base' => $s[3],
                'contract_type' => 'permanent',
                'start_date' => Carbon::create(rand(2012, 2020), 9, 1),
                'cin' => (string) rand(10000000, 14999999),
                'matricule_cnss' => (string) rand(1000000, 9999999),
                'rib' => '08 ' . rand(100, 999) . ' ' . rand(1000000000000, 9999999999999),
                'situation_familiale' => 'marie',
                'nb_enfants' => rand(1, 3),
                'indemnite_transport' => 60,
                'indemnite_logement' => 150,
                'autres_indemnites' => 0,
            ]));
        }
        return $out;
    }

    // ── Classrooms ─────────────────────────────────────────────────────────
    protected static function seedClassrooms($levels, $teachers, $subjects)
    {
        // (level index, suffix)
        $defs = [[0, 'A'], [0, 'B'], [1, 'A'], [1, 'B'], [2, 'A'], [3, 'A'], [4, 'A'], [5, 'A']];
        $out = collect();
        foreach ($defs as $k => $d) {
            $level = $levels[$d[0]];
            $class = Classroom::create([
                'name' => $level->code[0] . $d[1], // e.g. 1A
                'level_id' => $level->id,
                'teacher_id' => $teachers[$k % $teachers->count()]->id,
                'capacity' => 30,
                'notes' => 'Classe ' . $level->name . ' ' . $d[1],
            ]);
            // attach the full subject set to every class
            foreach ($subjects as $subject) {
                $class->subjects()->attach($subject->id, [
                    'weekly_hours' => max(1, (int) $subject->coefficient),
                    'coefficient' => $subject->coefficient,
                    'is_active' => true,
                ]);
            }
            $out->push($class);
        }
        return $out;
    }

    // ── Students ───────────────────────────────────────────────────────────
    protected static function seedStudents($classes)
    {
        $firstM = ['Mohamed', 'Ahmed', 'Youssef', 'Aziz', 'Rayan', 'Iyed', 'Adam', 'Skander', 'Nour', 'Hamza', 'Wassim', 'Bilel'];
        $firstF = ['Lina', 'Maryam', 'Eya', 'Farah', 'Selma', 'Nour', 'Yasmine', 'Rania', 'Aya', 'Malak', 'Hana', 'Sirine'];
        $last   = ['Ben Ali', 'Trabelsi', 'Gharbi', 'Jebali', 'Khelifi', 'Bouazizi', 'Mansour', 'Hamdi', 'Chaabane', 'Tlili', 'Belhadj', 'Sassi', 'Karoui', 'Maaloul', 'Ben Salah', 'Ayari'];

        $out = collect();
        foreach ($classes as $class) {
            $level = $class->level;
            $age = 5 + (int) $level->order; // 1AP ~ 6 yrs
            for ($n = 0; $n < 6; $n++) {
                $male = $n % 2 === 0;
                $first = $male ? $firstM[array_rand($firstM)] : $firstF[array_rand($firstF)];
                $out->push(Student::create([
                    'first_name' => $first,
                    'last_name' => $last[array_rand($last)],
                    'date_of_birth' => Carbon::create(now()->year - $age, rand(1, 12), rand(1, 28)),
                    'id_number' => 'EL' . str_pad((string) ($out->count() + 1), 5, '0', STR_PAD_LEFT),
                    'class' => $class->name,
                    'level' => $level->name,
                    'classroom_id' => $class->id,
                    'enrollment_date' => Carbon::create(2025, 9, rand(1, 15)),
                    'status' => 'active',
                    'address' => 'Tunis',
                ]));
            }
        }
        return $out;
    }

    // ── Parents ────────────────────────────────────────────────────────────
    protected static function seedParents($students)
    {
        $occupations = ['Ingénieur', 'Médecin', 'Enseignant', 'Commerçant', 'Fonctionnaire', 'Avocat', 'Infirmier', 'Artisan'];
        $out = collect();
        foreach ($students as $i => $student) {
            // one payer parent per student
            $parent = SchoolParent::create([
                'first_name' => ['Habib', 'Salah', 'Mongi', 'Tarek', 'Fethi', 'Kamel'][array_rand([0, 1, 2, 3, 4, 5])],
                'last_name' => $student->last_name,
                'phone' => '+216 ' . rand(20, 29) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                'email' => 'parent' . ($i + 1) . '@elamana.tn',
                'address' => 'Tunis',
                'occupation' => $occupations[array_rand($occupations)],
                'is_payer' => true,
            ]);
            $parent->students()->attach($student->id, ['relation' => 'father']);
            $out->push($parent);
        }
        return $out;
    }

    /**
     * Create login accounts so the demo can be tested from every role.
     * Teachers -> staff panel · Parents -> parent panel. Shared password.
     */
    protected static function seedAccounts($employees, $parents): void
    {
        foreach ($employees as $emp) {
            \App\Services\AccountService::forEmployee($emp, self::DEMO_PASSWORD, false);
        }
        // first 8 parents get a portal account (enough to demo)
        foreach ($parents->take(8) as $parent) {
            \App\Services\AccountService::forParent($parent, self::DEMO_PASSWORD, false);
        }
    }

    // ── Services ───────────────────────────────────────────────────────────
    protected static function seedServices()
    {
        $defs = [
            ['Frais d\'inscription', 'annual', 150], ['Scolarité mensuelle', 'monthly', 220],
            ['Transport scolaire', 'monthly', 80], ['Cantine', 'monthly', 120],
            ['Activités parascolaires', 'annual', 100],
        ];
        return collect($defs)->map(fn ($d) => Service::create([
            'name' => $d[0], 'type' => $d[1], 'amount' => $d[2],
            'is_active' => true, 'description' => $d[0],
        ]));
    }

    protected static function seedServiceSubscriptions($students, $services)
    {
        $scolarite = $services->firstWhere('name', 'Scolarité mensuelle');
        $transport = $services->firstWhere('name', 'Transport scolaire');
        $cantine   = $services->firstWhere('name', 'Cantine');
        foreach ($students as $i => $student) {
            $student->services()->attach($scolarite->id, ['start_date' => Carbon::create(2025, 9, 1)]);
            if ($i % 2 === 0) {
                $student->services()->attach($transport->id, ['start_date' => Carbon::create(2025, 9, 1)]);
            }
            if ($i % 3 === 0) {
                $student->services()->attach($cantine->id, ['start_date' => Carbon::create(2025, 9, 1)]);
            }
        }
    }

    // ── Payments (monthly scolarité, Sept -> now) ──────────────────────────
    protected static function seedPayments($students, $services)
    {
        $scolarite = $services->firstWhere('name', 'Scolarité mensuelle');
        $methods = ['cash', 'bank_transfer', 'cheque', 'app'];
        $start = Carbon::create(2025, 9, 1);
        $now = now();

        foreach ($students as $idx => $student) {
            $cursor = $start->copy();
            while ($cursor->lte($now)) {
                $due = $cursor->copy()->day(5);
                $isPast = $due->lt($now);
                // Most months paid; the latest 1-2 months for some students unpaid/overdue.
                $monthsAgo = $cursor->diffInMonths($now);
                $paid = $monthsAgo >= 1 ? (($idx + $cursor->month) % 7 !== 0) : (($idx % 3) !== 0);

                $payment = Payment::create([
                    'student_id' => $student->id,
                    'amount' => $scolarite->amount,
                    'due_date' => $due,
                    'payment_date' => $paid ? $due->copy()->addDays(rand(0, 6)) : $due,
                    'status' => $paid ? 'paid' : 'pending',
                    'payment_method' => $methods[array_rand($methods)],
                    'reference_number' => 'PAY-' . $cursor->format('Ym') . '-' . str_pad((string) ($student->id), 4, '0', STR_PAD_LEFT),
                    'reminders_count' => (! $paid && $isPast) ? rand(1, 2) : 0,
                    'notes' => 'Scolarité ' . $cursor->locale('fr')->isoFormat('MMMM YYYY'),
                ]);
                $payment->services()->attach($scolarite->id, ['amount' => $scolarite->amount]);
                $cursor->addMonth();
            }
        }
    }

    // ── Timetable (deterministic — same every seed) ────────────────────────
    protected static function seedTimetable($classes, $subjects, $teachers)
    {
        // Fixed daily slots (Tunisian primary: morning + afternoon, none Wed/Sat PM)
        $slots = [
            ['08:00', '09:00'], ['09:00', '10:00'], ['10:15', '11:15'], ['11:15', '12:15'],
            ['14:00', '15:00'], ['15:00', '16:00'],
        ];
        $days = TimetableEntry::$days; // Lundi..Samedi
        // teacher lookup by subject name
        $teacherFor = [];
        foreach ($subjects as $s) {
            $teacherFor[$s->name] = $teachers->first(fn ($t) => $t->specialite === $s->name)?->id
                ?? $teachers[array_rand($teachers->all())]->id;
        }
        // A fixed weekly pattern of subject codes (6 slots x 6 days)
        $weeklyPattern = [
            // Lundi
            ['Arabe', 'Arabe', 'Mathématiques', 'Français', 'Éveil scientifique', 'Anglais'],
            // Mardi
            ['Mathématiques', 'Français', 'Arabe', 'Éducation islamique', 'Éducation physique', 'Informatique'],
            // Mercredi (morning only)
            ['Arabe', 'Mathématiques', 'Français', 'Éveil scientifique', null, null],
            // Jeudi
            ['Français', 'Arabe', 'Mathématiques', 'Anglais', 'Éducation civique', 'Éducation artistique'],
            // Vendredi
            ['Arabe', 'Mathématiques', 'Français', 'Éveil scientifique', 'Éducation physique', 'Anglais'],
            // Samedi (morning only)
            ['Mathématiques', 'Arabe', 'Français', 'Informatique', null, null],
        ];

        foreach ($classes as $class) {
            foreach ($days as $di => $day) {
                foreach ($slots as $si => $slot) {
                    $subjectName = $weeklyPattern[$di][$si] ?? null;
                    if (! $subjectName) {
                        continue;
                    }
                    $subject = $subjects->firstWhere('name', $subjectName);
                    if (! $subject) {
                        continue;
                    }
                    TimetableEntry::create([
                        'classroom_id' => $class->id,
                        'subject_id' => $subject->id,
                        'employee_id' => $teacherFor[$subjectName] ?? null,
                        'day_of_week' => $day,
                        'start_time' => $slot[0],
                        'end_time' => $slot[1],
                        'room' => 'Salle ' . $class->name,
                        'academic_year' => self::ACADEMIC_YEAR,
                    ]);
                }
            }
        }
    }

    // ── Attendance (last 10 working days) ──────────────────────────────────
    protected static function seedAttendance($employees)
    {
        $day = now()->copy();
        $count = 0;
        while ($count < 10) {
            if (! $day->isSunday()) {
                foreach ($employees as $emp) {
                    $r = rand(1, 100);
                    $status = $r > 92 ? 'absent' : ($r > 84 ? 'late' : 'present');
                    Attendance::create([
                        'employee_id' => $emp->id,
                        'date' => $day->copy(),
                        'status' => $status,
                        'time_in' => $status === 'absent' ? null : ($status === 'late' ? '08:25' : '07:55'),
                        'time_out' => $status === 'absent' ? null : '16:05',
                        'total_hours' => $status === 'absent' ? 0 : 8,
                        'overtime_hours' => 0,
                    ]);
                }
                $count++;
            }
            $day->subDay();
        }
    }

    // ── Payrolls (current month, teachers) ─────────────────────────────────
    protected static function seedPayrolls($teachers)
    {
        $now = now();
        foreach ($teachers as $i => $emp) {
            $base = (float) $emp->salary_base;
            $cnss = round($base * 0.0918, 3);
            $cnssPat = round($base * 0.1657, 3);
            $foprolos = round($base * 0.01, 3);
            $irpp = round(max(0, ($base - $cnss) * 0.10), 3);
            $gross = $base + 60;
            $net = round($gross - $cnss - $irpp, 3);
            Payroll::create([
                'employee_id' => $emp->id,
                'month' => $now->month, 'year' => $now->year,
                'period_from' => $now->copy()->startOfMonth(),
                'period_to' => $now->copy()->endOfMonth(),
                'salary_base' => $base,
                'indemnite_transport' => 60,
                'gross_salary' => $gross,
                'cnss_deduction' => $cnss,
                'irpp_deduction' => $irpp,
                'cnss_patronale' => $cnssPat,
                'foprolos' => $foprolos,
                'total_charge_patronale' => round($cnssPat + $foprolos, 3),
                'net_salary' => $net,
                'status' => $i % 4 === 0 ? 'finalized' : 'paid',
                'notes' => 'Paie ' . $now->locale('fr')->isoFormat('MMMM YYYY'),
            ]);
        }
    }

    // ── Expense categories + expenses ──────────────────────────────────────
    protected static function seedExpenseCategories()
    {
        $defs = ['Salaires & charges', 'Loyer', 'Électricité & eau', 'Fournitures scolaires', 'Maintenance', 'Transport', 'Communication & marketing'];
        return collect($defs)->map(fn ($n) => ExpenseCategory::create(['name' => $n, 'description' => $n]));
    }

    protected static function seedExpenses($cats)
    {
        $suppliers = ['STEG', 'SONEDE', 'Librairie Ennour', 'Garage El Manar', 'Tunisie Telecom', 'Bureau Vallée'];
        $methods = ['cash', 'bank', 'cheque'];
        for ($m = 0; $m < 6; $m++) {
            $date = now()->copy()->subMonths($m);
            foreach ($cats as $cat) {
                if (rand(1, 100) > 70 && $cat->name !== 'Loyer' && $cat->name !== 'Électricité & eau') {
                    continue;
                }
                $amount = match ($cat->name) {
                    'Loyer' => 2800,
                    'Électricité & eau' => rand(400, 900),
                    'Salaires & charges' => rand(18000, 24000),
                    default => rand(150, 1200),
                };
                Expense::create([
                    'category_id' => $cat->id,
                    'amount' => $amount,
                    'date' => $date->copy()->day(rand(1, 27)),
                    'description' => $cat->name . ' — ' . $date->locale('fr')->isoFormat('MMMM YYYY'),
                    'supplier' => $suppliers[array_rand($suppliers)],
                    'payment_method' => $methods[array_rand($methods)],
                    'invoice_number' => 'FAC-' . $date->format('Ym') . '-' . rand(100, 999),
                ]);
            }
        }
    }

    // ── Incidents ──────────────────────────────────────────────────────────
    protected static function seedIncidents($students)
    {
        $defs = [
            ['Bavardage répété en classe', 'disciplinary', 'low'],
            ['Petite chute dans la cour', 'accident', 'medium'],
            ['Absence non justifiée', 'absence', 'low'],
            ['Dispute entre élèves', 'behavioral', 'medium'],
            ['Malaise — fièvre', 'health', 'high'],
            ['Oubli répété de matériel', 'disciplinary', 'low'],
        ];
        foreach ($defs as $i => $d) {
            $student = $students[($i * 5) % $students->count()];
            Incident::create([
                'student_id' => $student->id,
                'title' => $d[0],
                'description' => $d[0] . ' concernant ' . $student->first_name . ' ' . $student->last_name . '.',
                'type' => $d[1], 'severity' => $d[2],
                'incident_date' => now()->copy()->subDays(rand(1, 25)),
                'parent_notified' => $i % 2 === 0,
                'notification_sent_at' => $i % 2 === 0 ? now()->copy()->subDays(rand(1, 20)) : null,
                'action_taken' => $i % 2 === 0 ? 'Parent contacté par téléphone.' : null,
            ]);
        }
    }

    // ── Blog ───────────────────────────────────────────────────────────────
    protected static function seedBlog(): void
    {
        $author = \App\Models\User::first();
        $posts = [
            ['Rentrée scolaire 2025-2026', 'La rentrée aura lieu le lundi 15 septembre. Bienvenue à tous nos élèves !'],
            ['Réunion parents-enseignants', 'Une réunion est prévue le samedi matin pour échanger sur le suivi des élèves.'],
            ['Sortie pédagogique au musée du Bardo', 'Les classes de 5ème et 6ème année visiteront le musée du Bardo ce mois-ci.'],
            ['Vacances de l\'Aïd', 'L\'établissement sera fermé durant les jours de l\'Aïd. Bonnes fêtes à toutes les familles.'],
        ];
        foreach ($posts as $i => $p) {
            BlogPost::create([
                'title' => $p[0],
                'slug' => Str::slug($p[0]),
                'excerpt' => Str::limit($p[1], 90),
                'content' => $p[1],
                'author_id' => $author?->id,
                'is_published' => true,
                'published_at' => now()->copy()->subDays($i * 6 + 1),
            ]);
        }
    }
}
