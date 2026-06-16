<?php

use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'qr_token')) {
                $table->string('qr_token', 64)->nullable()->unique()->after('id_number');
            }
        });

        // Backfill tokens for existing students
        Student::whereNull('qr_token')->get()->each(function (Student $s) {
            $s->forceFill(['qr_token' => (string) Str::uuid()])->save();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'qr_token')) {
                $table->dropColumn('qr_token');
            }
        });
    }
};
