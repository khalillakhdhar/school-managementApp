<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TimetableEntry;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Gate;

class StudentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index()
    {
        Gate::authorize('viewAny', Student::class);

        $user = request()->user();
        $query = Student::activeOnly();

        if ($user->isParent()) {
            $studentIds = $user->parent?->students()?->pluck('students.id') ?? collect();
            $query->whereIn('id', $studentIds);
        } elseif ($user->isTeacher() && ! $user->isAdmin()) {
            $classroomIds = TimetableEntry::where('employee_id', $user->employee?->id)
                ->pluck('classroom_id')
                ->unique();
            $query->whereIn('classroom_id', $classroomIds);
        }

        return $query->paginate(15);
    }

    public function show(Student $student)
    {
        Gate::authorize('view', $student);

        return $student->load(['parents', 'services', 'payments']);
    }

    public function store()
    {
        Gate::authorize('create', Student::class);

        $validated = request()->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'class' => 'required|string',
            'level' => 'required|string',
        ]);
        return Student::create($validated);
    }

    public function update(Student $student)
    {
        Gate::authorize('update', $student);

        $validated = request()->validate([
            'first_name' => 'string',
            'last_name' => 'string',
            'date_of_birth' => 'date',
            'class' => 'string',
            'level' => 'string',
            'status' => 'in:active,inactive',
        ]);
        $student->update($validated);
        return $student;
    }

    public function destroy(Student $student)
    {
        Gate::authorize('delete', $student);

        $student->delete();
        return response()->noContent();
    }

    public function getBalance(Student $student)
    {
        Gate::authorize('view', $student);

        return $this->paymentService->getStudentBalance($student);
    }

    public function getPayments(Student $student)
    {
        Gate::authorize('view', $student);

        return $student->payments()->latest('payment_date')->paginate(10);
    }
}
