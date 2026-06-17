<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class EmployeePolicy
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

    public function view(User $user, Employee $employee): bool
    {
        return $user->employee?->id === $employee->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->employee?->id === $employee->id;
    }

    public function delete(User $user, Employee $employee): bool
    {
        return false;
    }
}
