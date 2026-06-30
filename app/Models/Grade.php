<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use Auditable, BelongsToSchool;

    public function auditLabel(): string
    {
        return 'Note ' . $this->term . ' — ' . $this->score . '/' . $this->max_score;
    }

    protected $fillable = [
        'school_id',
        'student_id', 'subject_id', 'classroom_id', 'employee_id',
        'term', 'score', 'max_score', 'coefficient', 'date', 'comment',
    ];

    protected $casts = [
        'score'       => 'decimal:2',
        'max_score'   => 'decimal:2',
        'coefficient' => 'decimal:2',
        'date'        => 'date',
    ];

    public static array $terms = ['T1' => '1er trimestre', 'T2' => '2e trimestre', 'T3' => '3e trimestre'];

    public function student(): BelongsTo  { return $this->belongsTo(Student::class); }
    public function subject(): BelongsTo   { return $this->belongsTo(Subject::class); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function employee(): BelongsTo  { return $this->belongsTo(Employee::class); }

    /** Note ramenée sur 20. */
    public function getNormalizedAttribute(): float
    {
        $max = (float) $this->max_score ?: 20;
        return round((float) $this->score / $max * 20, 2);
    }
}
