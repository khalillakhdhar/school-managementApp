<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('salary_base', 10, 3);
            $table->decimal('overtime_pay', 10, 3)->default(0);
            $table->decimal('bonuses', 10, 3)->default(0);
            $table->decimal('cnss_deduction', 10, 3)->default(0);
            $table->decimal('irpp_deduction', 10, 3)->default(0);
            $table->decimal('other_deductions', 10, 3)->default(0);
            $table->decimal('gross_salary', 10, 3);
            $table->decimal('net_salary', 10, 3);
            $table->enum('status', ['draft', 'finalized', 'paid', 'rejected'])->default('draft');
            $table->string('fiche_de_paie_path')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'month', 'year']);
            $table->index(['employee_id', 'status']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('payrolls');
    }
};
