<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = ['name', 'code', 'order', 'description'];

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }
}
