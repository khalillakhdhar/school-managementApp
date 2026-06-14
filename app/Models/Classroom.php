<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $fillable = ['name', 'level_id', 'teacher_id', 'capacity', 'notes'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->level ? "{$this->level->code} — {$this->name}" : $this->name;
    }
}
