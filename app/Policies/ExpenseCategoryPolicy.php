<?php

namespace App\Policies;

use App\Models\ExpenseCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class ExpenseCategoryPolicy
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

    public function view(User $user, ExpenseCategory $expenseCategory): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ExpenseCategory $expenseCategory): bool
    {
        return false;
    }

    public function delete(User $user, ExpenseCategory $expenseCategory): bool
    {
        return false;
    }
}
