<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'date_of_birth', 'id_number',
        'class', 'level', 'status', 'address', 'health_info',
        'allergies', 'medications', 'photo_path',
    ];

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(SchoolParent::class, 'parent_student', 'student_id', 'parent_id')
                    ->withPivot('relation')->withTimestamps();
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_student')
                    ->withPivot('amount_override', 'start_date', 'end_date')->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getTotalOutstandingAttribute(): float
    {
        $totalDue = $this->services()->sum('services.amount');
        $totalPaid = $this->payments()->where('status', 'paid')->sum('amount');
        return max(0, $totalDue - $totalPaid);
    }

    public function scopeActiveOnly($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByClass($query, $class)
    {
        return $query->where('class', $class);
    }
}
