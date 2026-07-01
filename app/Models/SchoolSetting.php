<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSetting extends Model
{
    // Phase 8: per-tenant settings. getInstance() resolves the row for the
    // current tenant (one settings row per school). No global scope trait —
    // getInstance() filters by school_id explicitly, and admin/CLI code that
    // needs another school wraps its work in Tenancy::runFor().
    protected $fillable = [
        'school_id',
        'school_name', 'slogan', 'description', 'logo', 'favicon',
        'address', 'city', 'country', 'phone', 'mobile', 'email', 'website',
        'facebook', 'instagram', 'linkedin', 'youtube',
        'academic_year', 'school_type', 'demo_mode',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    protected $casts = [
        'demo_mode' => 'boolean',
    ];

    public static function getInstance(): static
    {
        try {
            // Per-tenant: resolve (or lazily create) the settings row for the
            // current school. Falls back to the legacy singleton (id = 1) when
            // there is no active tenant (public landing, generic CLI).
            $schoolId = \App\Support\Tenancy::id();

            if ($schoolId !== null) {
                return static::firstOrCreate(
                    ['school_id' => $schoolId],
                    ['school_name' => \App\Support\Tenancy::current()?->name ?? config('app.name', 'EliteCampus')]
                );
            }

            return static::firstOrCreate(
                ['id' => 1],
                ['school_name' => config('app.name', 'EliteCampus')]
            );
        } catch (\Throwable) {
            $instance = new static();
            $instance->school_name = config('app.name', 'EliteCampus');
            return $instance;
        }
    }

    public static function get(string $field, mixed $default = null): mixed
    {
        return static::getInstance()->$field ?? $default;
    }
}
