<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableEntry extends Model
{
    protected $fillable = [
        'classroom_id', 'subject_id', 'employee_id',
        'day_of_week', 'start_time', 'end_time',
        'room', 'notes', 'academic_year',
    ];

    protected $casts = [
        'start_time' => 'string',
        'end_time'   => 'string',
    ];

    public static array $days = [
        'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getDurationMinutesAttribute(): int
    {
        [$sh, $sm] = explode(':', $this->start_time);
        [$eh, $em] = explode(':', $this->end_time);
        return (int)$eh * 60 + (int)$em - (int)$sh * 60 - (int)$sm;
    }

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration_minutes / 60, 2);
    }

    public function overlaps(self $other): bool
    {
        return $this->day_of_week === $other->day_of_week
            && $this->start_time < $other->end_time
            && $this->end_time   > $other->start_time;
    }

    public function scopeForClassroom($query, int $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    public function scopeForTeacher($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForDay($query, string $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['classroom', 'subject', 'teacher']);
    }
}
