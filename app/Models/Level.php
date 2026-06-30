<?php
namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'name', 'code', 'order', 'description'];

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }
}
