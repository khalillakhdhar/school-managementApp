<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class IncidentPolicy
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

    public function view(User $user, Incident $incident): bool
    {
        return ($incident->student && $this->parentOwnsStudent($user, $incident->student))
            || ($incident->student && $this->teacherHandlesStudent($user, $incident->student));
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['teacher', 'employee'], true);
    }

    public function update(User $user, Incident $incident): bool
    {
        return $incident->student
            ? $this->teacherHandlesStudent($user, $incident->student)
            : false;
    }

    public function delete(User $user, Incident $incident): bool
    {
        return false;
    }
}
