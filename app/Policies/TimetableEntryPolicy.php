<?php

namespace App\Policies;

use App\Models\TimetableEntry;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class TimetableEntryPolicy
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

    public function view(User $user, TimetableEntry $timetableEntry): bool
    {
        return $this->teacherHandlesClassSubject(
            $user,
            $timetableEntry->classroom_id,
            $timetableEntry->subject_id
        );
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, TimetableEntry $timetableEntry): bool
    {
        return false;
    }

    public function delete(User $user, TimetableEntry $timetableEntry): bool
    {
        return false;
    }
}
