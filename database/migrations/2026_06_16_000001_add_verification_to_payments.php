<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('status');
                $table->timestamp('verified_at')->nullable()->after('is_verified');
                $table->foreignId('verified_by')->nullable()->after('verified_at')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'is_verified')) {
                $table->dropConstrainedForeignId('verified_by');
                $table->dropColumn(['is_verified', 'verified_at']);
            }
        });
    }
};
