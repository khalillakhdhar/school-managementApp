<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('amount', 10, 3);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'app'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->enum('status', ['paid', 'pending', 'failed', 'cancelled'])->default('paid');
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'payment_date', 'status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
