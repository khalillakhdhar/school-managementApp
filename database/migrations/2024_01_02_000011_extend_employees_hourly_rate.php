<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Taux horaire pour les prestataires (contract_type = 'contract')
            $table->decimal('hourly_rate', 10, 3)->default(0)->after('salary_base');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('hourly_rate');
        });
    }
};
