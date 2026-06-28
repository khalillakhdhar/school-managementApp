<?php

namespace App\Policies;

use App\Models\Level;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class LevelPolicy
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

    public function view(User $user, Level $level): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Level $level): bool
    {
        return false;
    }

    public function delete(User $user, Level $level): bool
    {
        return false;
    }
}
