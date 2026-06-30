<?php

namespace App\Support;

use App\Models\School;
use Closure;
use Filament\Facades\Filament;

/**
 * Central tenant-context resolver.
 *
 * Resolves the "current school" from either:
 *  - the Filament tenant (web requests inside a tenant-aware panel), or
 *  - an app-bound `currentSchool` instance (CLI, queue jobs, scheduled commands).
 *
 * The BelongsToSchool global scope reads Tenancy::id(); CLI/queue code that must
 * operate on a given school wraps its work in Tenancy::runFor() / eachSchool().
 */
class Tenancy
{
    private const KEY = 'currentSchool';

    public static function current(): ?School
    {
        // Filament tenant wins in a web request; null/throws safely in CLI.
        try {
            $tenant = Filament::getTenant();
            if ($tenant instanceof School) {
                return $tenant;
            }
        } catch (\Throwable) {
            // No current panel (CLI) — fall through to the bound context.
        }

        return app()->bound(self::KEY) ? app(self::KEY) : null;
    }

    public static function id(): ?int
    {
        return static::current()?->getKey();
    }

    public static function check(): bool
    {
        return static::current() !== null;
    }

    /**
     * Run a callback with the given school as the active tenant (CLI/queue).
     * Restores the previous context afterwards (nestable).
     */
    public static function runFor(School $school, Closure $callback): mixed
    {
        $previous = app()->bound(self::KEY) ? app(self::KEY) : null;
        app()->instance(self::KEY, $school);

        try {
            return $callback($school);
        } finally {
            if ($previous instanceof School) {
                app()->instance(self::KEY, $previous);
            } else {
                app()->forgetInstance(self::KEY);
            }
        }
    }

    /**
     * Run a callback once per live school (active or on trial), each within its
     * own tenant context. Use for scheduled commands and batch jobs.
     */
    public static function eachSchool(Closure $callback): void
    {
        School::query()
            ->whereIn('status', [School::STATUS_ACTIVE, School::STATUS_TRIAL])
            ->orderBy('id')
            ->get()
            ->each(fn (School $school) => static::runFor($school, $callback));
    }
}
