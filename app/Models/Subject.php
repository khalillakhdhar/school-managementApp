<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name', 'code', 'description', 'coefficient', 'color', 'is_active',
    ];

    protected $casts = [
        'coefficient' => 'decimal:2',
        'is_active'   => 'boolean',
    ];

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_subject')
            ->withPivot('weekly_hours', 'coefficient', 'is_active')
            ->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_subject')
            ->withPivot('specialization', 'max_hours_per_week')
            ->withTimestamps();
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getWeeklyHoursAttribute(): float
    {
        return (float) $this->timetableEntries()
            ->selectRaw('SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) / 3600 as total')
            ->value('total') ?? 0;
    }
}
