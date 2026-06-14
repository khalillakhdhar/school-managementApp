<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Période personnalisée (utilisée pour les prestataires)
            $table->date('period_from')->nullable()->after('year');
            $table->date('period_to')->nullable()->after('period_from');
            // Données horaires prestataire
            $table->decimal('total_hours_worked', 8, 2)->default(0)->after('period_to');
            $table->decimal('hourly_rate_used', 10, 3)->default(0)->after('total_hours_worked');
            // Retenue à la source (RS) — applicable aux honoraires prestataires en Tunisie (15%)
            $table->decimal('retenue_source', 10, 3)->default(0)->after('other_deductions');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'period_from', 'period_to',
                'total_hours_worked', 'hourly_rate_used',
                'retenue_source',
            ]);
        });
    }
};
