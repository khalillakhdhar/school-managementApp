<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('is_teacher')->default(false)->after('is_active');
            $table->string('cin', 20)->nullable()->after('is_teacher');
            $table->string('matricule_cnss', 30)->nullable()->after('cin');
            $table->string('rib', 24)->nullable()->after('matricule_cnss');
            $table->string('specialite')->nullable()->after('rib');
            $table->enum('situation_familiale', ['celibataire', 'marie', 'divorce', 'veuf'])
                ->default('celibataire')->after('specialite');
            $table->unsignedTinyInteger('nb_enfants')->default(0)->after('situation_familiale');
            $table->decimal('indemnite_transport', 10, 3)->default(0)->after('nb_enfants');
            $table->decimal('indemnite_logement', 10, 3)->default(0)->after('indemnite_transport');
            $table->decimal('autres_indemnites', 10, 3)->default(0)->after('indemnite_logement');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'is_teacher', 'cin', 'matricule_cnss', 'rib', 'specialite',
                'situation_familiale', 'nb_enfants',
                'indemnite_transport', 'indemnite_logement', 'autres_indemnites',
            ]);
        });
    }
};
