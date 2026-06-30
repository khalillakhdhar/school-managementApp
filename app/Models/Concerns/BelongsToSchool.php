<?php

namespace App\Models\Concerns;

use App\Models\School;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PHASE 0 SPIKE — tenant scoping based on the current Filament tenant.
 *
 * Defence-in-depth on top of Filament's own resource scoping:
 *  - read  : a global scope filters every query by the current tenant's id
 *  - write : the creating event stamps school_id from the current tenant
 *
 * When there is no Filament tenant (CLI, queue, API, super-admin /platform),
 * the scope is a no-op — those contexts must set the tenant explicitly
 * (Filament::setTenant) or be allowed to see across tenants on purpose.
 */
trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder): void {
            $tenant = Filament::getTenant();

            if ($tenant instanceof School) {
                $builder->where(
                    $builder->getModel()->getTable() . '.school_id',
                    $tenant->getKey()
                );
            }
        });

        static::creating(function ($model): void {
            if (! $model->school_id && ($tenant = Filament::getTenant()) instanceof School) {
                $model->school_id = $tenant->getKey();
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
