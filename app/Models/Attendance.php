<?php
namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'employee_id', 'date', 'status', 'time_in',
        'time_out', 'total_hours', 'overtime_hours', 'notes',
    ];

    protected $casts = ['date' => 'date'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
