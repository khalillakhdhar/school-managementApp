<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['date', 'name', 'type', 'description'];

    protected $casts = ['date' => 'date'];

    public static array $typeLabels = [
        'national'  => 'Jour férié national',
        'religieux' => 'Fête religieuse',
        'scolaire'  => 'Vacances scolaires',
    ];
}
