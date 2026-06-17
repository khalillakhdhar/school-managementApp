<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class GradePolicy
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

    public function view(User $user, Grade $grade): bool
    {
        return ($grade->student && $this->parentOwnsStudent($user, $grade->student))
            || $this->teacherHandlesClassSubject($user, $grade->classroom_id, $grade->subject_id);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['teacher', 'employee'], true);
    }

    public function update(User $user, Grade $grade): bool
    {
        return $this->teacherHandlesClassSubject($user, $grade->classroom_id, $grade->subject_id);
    }

    public function delete(User $user, Grade $grade): bool
    {
        return false;
    }
}
