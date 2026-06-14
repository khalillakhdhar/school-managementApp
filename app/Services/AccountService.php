<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SchoolParent;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Provisions login accounts for parents and employees (teachers/staff).
 * Returns the generated plaintext password so the admin can hand it over.
 */
class AccountService
{
    /** Create (or reuse) a User account for a parent and link it. */
    public static function forParent(SchoolParent $parent, ?string $password = null, bool $mustChange = true): array
    {
        $email = $parent->email ?: self::fallbackEmail($parent->first_name, $parent->last_name, 'parent');
        $password ??= self::randomPassword();

        $user = User::firstOrNew(['email' => $email]);
        $user->fill([
            'name'                 => trim($parent->first_name . ' ' . $parent->last_name),
            'role'                 => 'parent',
            'password'             => Hash::make($password),
            'must_change_password' => $mustChange,
        ])->save();

        $parent->forceFill(['user_id' => $user->id])->save();

        return ['user' => $user, 'email' => $email, 'password' => $password];
    }

    /** Create (or reuse) a User account for an employee/teacher and link it. */
    public static function forEmployee(Employee $employee, ?string $password = null, bool $mustChange = true): array
    {
        $email = $employee->email ?: self::fallbackEmail($employee->first_name, $employee->last_name, 'staff');
        $password ??= self::randomPassword();

        $user = User::firstOrNew(['email' => $email]);
        $user->fill([
            'name'                 => trim($employee->first_name . ' ' . $employee->last_name),
            'role'                 => $employee->is_teacher ? 'teacher' : 'employee',
            'password'             => Hash::make($password),
            'must_change_password' => $mustChange,
        ])->save();

        $employee->forceFill(['user_id' => $user->id])->save();

        return ['user' => $user, 'email' => $email, 'password' => $password];
    }

    public static function randomPassword(int $length = 10): string
    {
        return Str::password($length, symbols: false);
    }

    protected static function fallbackEmail(string $first, string $last, string $kind): string
    {
        $base = Str::slug($first . '.' . $last);
        $base = $base !== '' ? $base : $kind . Str::random(4);

        return $base . '@elamana.tn';
    }
}
