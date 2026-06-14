<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('parents', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('is_payer')
                  ->constrained('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
