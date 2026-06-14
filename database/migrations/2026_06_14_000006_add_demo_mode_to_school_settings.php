<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('school_settings', 'demo_mode')) {
                $table->boolean('demo_mode')->default(false)->after('school_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            if (Schema::hasColumn('school_settings', 'demo_mode')) {
                $table->dropColumn('demo_mode');
            }
        });
    }
};
