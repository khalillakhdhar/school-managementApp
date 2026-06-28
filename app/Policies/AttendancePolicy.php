<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class AttendancePolicy
{
    use ChecksSchoolAccess;

    public function before(User $user): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['teacher', 'employee'], true);
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->employee?->id === $attendance->employee_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['teacher', 'employee'], true);
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->employee?->id === $attendance->employee_id;
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return false;
    }
}
