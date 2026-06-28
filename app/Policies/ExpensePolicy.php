<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class ExpensePolicy
{
    use ChecksSchoolAccess;

    public function before(User $user): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Expense $expense): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Expense $expense): bool
    {
        return false;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return false;
    }
}
