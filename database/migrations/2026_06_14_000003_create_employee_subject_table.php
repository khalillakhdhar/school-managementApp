<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('specialization')->nullable();
            $table->unsignedTinyInteger('max_hours_per_week')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_subject');
    }
};
