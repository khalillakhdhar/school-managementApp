<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'school_name', 'slogan', 'description', 'logo', 'favicon',
        'address', 'city', 'country', 'phone', 'mobile', 'email', 'website',
        'facebook', 'instagram', 'linkedin', 'youtube',
        'academic_year', 'school_type',
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
