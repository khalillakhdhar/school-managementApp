<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                           // e.g. "1A", "2B"
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->unsignedSmallInteger('capacity')->default(30);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['level_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('classrooms');
    }
};
