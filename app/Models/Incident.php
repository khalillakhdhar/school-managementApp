<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = [
        'student_id', 'title', 'description', 'type', 'severity',
        'incident_date', 'parent_notified', 'notification_sent_at', 'action_taken',
    ];

    protected $casts = [
        'incident_date'        => 'date',
        'notification_sent_at' => 'datetime',
        'parent_notified'      => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
