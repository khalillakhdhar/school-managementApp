<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('term', ['T1', 'T2', 'T3'])->default('T1');
            $table->decimal('score', 5, 2);
            $table->decimal('max_score', 5, 2)->default(20);
            $table->decimal('coefficient', 4, 2)->default(1);
            $table->date('date')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'term']); // une note /matière /trimestre
            $table->index(['classroom_id', 'term']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
