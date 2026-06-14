<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    protected $fillable = [
        'student_id', 'amount', 'payment_date', 'due_date', 'payment_method',
        'reference_number', 'status', 'notes', 'receipt_path',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'due_date'     => 'date',
        'amount'       => 'decimal:3',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'payment_service')->withPivot('amount')->withTimestamps();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByMonth($query, $month, $year)
    {
        return $query->whereMonth('payment_date', $month)->whereYear('payment_date', $year);
    }
}
