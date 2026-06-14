<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->decimal('amount', 10, 3);
            $table->date('date');
            $table->string('description')->nullable();
            $table->string('supplier')->nullable();
            $table->enum('payment_method', ['cash', 'bank', 'cheque'])->default('cash');
            $table->string('invoice_number')->nullable();
            $table->string('invoice_image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['category_id', 'date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('expenses');
    }
};
