<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class ClassroomPolicy
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

    public function view(User $user, Classroom $classroom): bool
    {
        return $this->teacherHandlesClassSubject($user, $classroom->id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return false;
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return false;
    }
}
