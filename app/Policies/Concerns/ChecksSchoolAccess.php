<?php

namespace App\Policies\Concerns;

use App\Models\Student;
use App\Models\TimetableEntry;
use App\Models\User;

trait ChecksSchoolAccess
{
    protected function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    protected function parentOwnsStudent(User $user, Student $student): bool
    {
        return $user->parent?->students()
            ->whereKey($student->getKey())
            ->exists() ?? false;
    }

    protected function teacherHandlesStudent(User $user, Student $student): bool
    {
        $employee = $user->employee;

        if (! $employee || ! $student->classroom_id) {
            return false;
        }

        return TimetableEntry::where('employee_id', $employee->id)
            ->where('classroom_id', $student->classroom_id)
            ->exists();
    }

    protected function teacherHandlesClassSubject(User $user, ?int $classroomId, ?int $subjectId = null): bool
    {
        $employee = $user->employee;

        if (! $employee || ! $classroomId) {
            return false;
        }

        return TimetableEntry::where('employee_id', $employee->id)
            ->where('classroom_id', $classroomId)
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->exists();
    }
}
