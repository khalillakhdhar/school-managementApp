<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class StudentPolicy
{
    use ChecksSchoolAccess;

    public function before(User $user): ?bool
    {
        return $this->isAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['parent', 'teacher', 'employee'], true);
    }

    public function view(User $user, Student $student): bool
    {
        return $this->parentOwnsStudent($user, $student)
            || $this->teacherHandlesStudent($user, $student);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Student $student): bool
    {
        return false;
    }

    public function delete(User $user, Student $student): bool
    {
        return false;
    }
}
