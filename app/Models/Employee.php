<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'first_name', 'last_name', 'position', 'phone', 'email',
        'address', 'salary_base', 'hourly_rate', 'contract_type',
        'start_date', 'end_date', 'photo_path', 'is_active',
        // Teacher & payroll fields
        'is_teacher', 'cin', 'matricule_cnss', 'rib', 'specialite',
        'situation_familiale', 'nb_enfants',
        'indemnite_transport', 'indemnite_logement', 'autres_indemnites',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasAccount(): bool
    {
        return $this->user_id !== null;
    }

    protected $casts = [
        'start_date'           => 'date',
        'end_date'             => 'date',
        'salary_base'          => 'decimal:3',
        'hourly_rate'          => 'decimal:3',
        'indemnite_transport'  => 'decimal:3',
        'indemnite_logement'   => 'decimal:3',
        'autres_indemnites'    => 'decimal:3',
        'is_active'            => 'boolean',
        'is_teacher'           => 'boolean',
        'nb_enfants'           => 'integer',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'employee_subject')
            ->withPivot('specialization', 'max_hours_per_week')
            ->withTimestamps();
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class, 'employee_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getTotalAllowancesAttribute(): float
    {
        return (float)$this->indemnite_transport
             + (float)$this->indemnite_logement
             + (float)$this->autres_indemnites;
    }

    public function isContractor(): bool
    {
        return $this->contract_type === 'contract';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTeachers($query)
    {
        return $query->where('is_teacher', true);
    }

    public function scopeContractors($query)
    {
        return $query->where('contract_type', 'contract');
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }
}
