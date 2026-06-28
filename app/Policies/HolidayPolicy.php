<?php

namespace App\Policies;

use App\Models\Holiday;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class HolidayPolicy
{
    use ChecksSchoolAccess;

    public function before(User $user): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Holiday $holiday): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return false;
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return false;
    }
}
