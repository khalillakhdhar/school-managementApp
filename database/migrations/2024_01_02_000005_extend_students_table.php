<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('classroom_id')->nullable()->after('level')
                  ->constrained('classrooms')->nullOnDelete();
            $table->date('enrollment_date')->nullable()->after('classroom_id');
        });

        // Extend status enum to add suspended and graduated
        DB::statement("ALTER TABLE students MODIFY COLUMN status ENUM('active','inactive','suspended','graduated') DEFAULT 'active'");
    }

    public function down(): void {
        DB::statement("ALTER TABLE students MODIFY COLUMN status ENUM('active','inactive') DEFAULT 'active'");

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropColumn(['classroom_id', 'enrollment_date']);
        });
    }
};
