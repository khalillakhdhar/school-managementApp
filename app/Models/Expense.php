<?php
namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'category_id', 'amount', 'date', 'description', 'supplier',
        'payment_method', 'invoice_number', 'invoice_image_path', 'notes',
    ];

    protected $casts = ['date' => 'date', 'amount' => 'decimal:3'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}
