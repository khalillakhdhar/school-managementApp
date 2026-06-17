<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class PayrollPolicy
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

    public function view(User $user, Payroll $payroll): bool
    {
        return $user->employee?->id === $payroll->employee_id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Payroll $payroll): bool
    {
        return false;
    }

    public function delete(User $user, Payroll $payroll): bool
    {
        return false;
    }
}
