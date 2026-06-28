<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Policies\Concerns\ChecksSchoolAccess;

class SubjectPolicy
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

    public function view(User $user, Subject $subject): bool
    {
        $employee = $user->employee;

        if (! $employee) {
            return false;
        }

        return TimetableEntry::where('employee_id', $employee->id)
            ->where('subject_id', $subject->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Subject $subject): bool
    {
        return false;
    }

    public function delete(User $user, Subject $subject): bool
    {
        return false;
    }
}
