<?php

namespace App\Models\Concerns;

use App\Models\School;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant scoping based on the current tenant resolved by App\Support\Tenancy
 * (Filament tenant in web requests, or an app-bound school in CLI/queue).
 *
 * Defence-in-depth on top of Filament's own resource scoping:
 *  - read  : a global scope filters every query by the current tenant's id
 *  - write : the creating event stamps school_id from the current tenant
 *
 * With no current tenant (plain CLI, super-admin /platform), the scope is a
 * no-op — those contexts see across tenants on purpose, or wrap their work in
 * Tenancy::runFor() / Tenancy::eachSchool() to opt into a tenant.
 */
trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder): void {
            if ($schoolId = Tenancy::id()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.school_id',
                    $schoolId
                );
            }
        });

        static::creating(function ($model): void {
            if (! $model->school_id && $schoolId = Tenancy::id()) {
                $model->school_id = $schoolId;
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
