<?php
namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    use Auditable;

    public function auditLabel(): string
    {
        return 'Paiement #' . $this->id . ' — ' . number_format((float) $this->amount, 3) . ' TND';
    }

    protected $fillable = [
        'student_id', 'amount', 'payment_date', 'due_date', 'payment_method',
        'reference_number', 'status', 'notes', 'receipt_path',
        'is_verified', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'due_date'     => 'date',
        'amount'       => 'decimal:3',
        'is_verified'  => 'boolean',
        'verified_at'  => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
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
