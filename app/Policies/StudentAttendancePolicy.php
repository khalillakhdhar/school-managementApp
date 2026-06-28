<?php

namespace App\Policies;

use App\Models\StudentAttendance;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class StudentAttendancePolicy
{
    use ChecksSchoolAccess;

    public function before(User $user): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['teacher', 'employee', 'parent'], true);
    }

    public function view(User $user, StudentAttendance $studentAttendance): bool
    {
        if ($user->role === 'parent') {
            return $studentAttendance->student
                ? $this->parentOwnsStudent($user, $studentAttendance->student)
                : false;
        }

        return $this->teacherHandlesClassSubject($user, $studentAttendance->classroom_id);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['teacher', 'employee'], true);
    }

    public function update(User $user, StudentAttendance $studentAttendance): bool
    {
        return $this->teacherHandlesClassSubject($user, $studentAttendance->classroom_id);
    }

    public function delete(User $user, StudentAttendance $studentAttendance): bool
    {
        return false;
    }
}
