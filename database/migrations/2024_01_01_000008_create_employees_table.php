<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('position');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->decimal('salary_base', 10, 3);
            $table->enum('contract_type', ['permanent', 'temporary', 'contract'])->default('permanent');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['position', 'is_active']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('employees');
    }
};
