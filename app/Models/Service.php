<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = ['name', 'type', 'amount', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'amount' => 'decimal:3'];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'service_student')
                    ->withPivot('amount_override', 'start_date', 'end_date')->withTimestamps();
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_service')->withPivot('amount')->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
