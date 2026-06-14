<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolParent extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'first_name', 'last_name', 'phone', 'email',
        'address', 'occupation', 'is_payer', 'user_id',
    ];

    protected $casts = ['is_payer' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
                    ->withPivot('relation')->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function hasAccount(): bool
    {
        return $this->user_id !== null;
    }
}
