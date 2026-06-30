<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSetting extends Model
{
    // NOTE (Phase 2): school_id column + relationship added, but NO global scope
    // trait yet — this model is a singleton resolved by getInstance(). The full
    // per-tenant rewrite of getInstance() is Phase 8.
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
