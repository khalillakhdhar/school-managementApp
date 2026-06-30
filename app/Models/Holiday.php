<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'date', 'name', 'type', 'description'];

    protected $casts = ['date' => 'date'];

    public static array $typeLabels = [
        'national'  => 'Jour férié national',
        'religieux' => 'Fête religieuse',
        'scolaire'  => 'Vacances scolaires',
    ];
}
