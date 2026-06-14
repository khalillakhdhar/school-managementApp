<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['accident', 'health', 'disciplinary', 'absence', 'behavioral', 'other'])->default('other');
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->date('incident_date');
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            $table->text('action_taken')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'incident_date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('incidents');
    }
};
