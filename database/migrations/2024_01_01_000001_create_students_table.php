<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('id_number')->nullable();
            $table->string('class');
            $table->string('level');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('address')->nullable();
            $table->text('health_info')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medications')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->index(['class', 'status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('students');
    }
};
