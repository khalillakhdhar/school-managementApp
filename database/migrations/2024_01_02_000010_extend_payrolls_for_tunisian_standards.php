<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Indemnités (après bonuses)
            $table->decimal('indemnite_transport', 10, 3)->default(0)->after('bonuses');
            $table->decimal('indemnite_logement', 10, 3)->default(0)->after('indemnite_transport');
            $table->decimal('autres_indemnites', 10, 3)->default(0)->after('indemnite_logement');
            // Charges patronales (après other_deductions)
            $table->decimal('cnss_patronale', 10, 3)->default(0)->after('other_deductions');
            $table->decimal('foprolos', 10, 3)->default(0)->after('cnss_patronale');
            $table->decimal('total_charge_patronale', 10, 3)->default(0)->after('foprolos');
            // Notes
            $table->text('notes')->nullable()->after('fiche_de_paie_path');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'indemnite_transport', 'indemnite_logement', 'autres_indemnites',
                'cnss_patronale', 'foprolos', 'total_charge_patronale', 'notes',
            ]);
        });
    }
};
