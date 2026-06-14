<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payment_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->decimal('amount', 10, 3);
            $table->timestamps();
            $table->unique(['payment_id', 'service_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('payment_service');
    }
};
