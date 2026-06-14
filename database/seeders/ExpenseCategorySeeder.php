<?php
namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Loyer / Location',           'description' => 'Loyer mensuel des locaux scolaires'],
            ['name' => 'Électricité',                 'description' => 'Factures d\'électricité'],
            ['name' => 'Eau et Assainissement',       'description' => 'Factures d\'eau et assainissement'],
            ['name' => 'Internet / Téléphone',        'description' => 'Abonnements internet et téléphone'],
            ['name' => 'Fournitures de Bureau',       'description' => 'Achats de fournitures administratives'],
            ['name' => 'Matériel Pédagogique',        'description' => 'Livres, tableaux, matériel scolaire'],
            ['name' => 'Maintenance et Entretien',    'description' => 'Entretien régulier des locaux et équipements'],
            ['name' => 'Réparations',                 'description' => 'Réparations urgentes et remise en état'],
            ['name' => 'Taxes et Impôts',             'description' => 'Taxes locales, impôts professionnels'],
            ['name' => 'Assurance',                   'description' => 'Assurance scolaire et responsabilité civile'],
            ['name' => 'Transport Scolaire',          'description' => 'Carburant, entretien des véhicules scolaires'],
            ['name' => 'Restauration / Cantine',      'description' => 'Achats alimentaires pour la cantine'],
            ['name' => 'Événements et Fêtes',         'description' => 'Organisation de fêtes, sorties, événements'],
            ['name' => 'Publicité et Communication',  'description' => 'Publicité, impression, communication'],
            ['name' => 'Formation du Personnel',      'description' => 'Formations et séminaires pour les employés'],
            ['name' => 'Frais Bancaires',             'description' => 'Commissions et frais bancaires'],
            ['name' => 'Frais d\'Inscription',        'description' => 'Frais administratifs d\'inscription'],
            ['name' => 'Nettoyage et Hygiène',        'description' => 'Produits et services de nettoyage'],
            ['name' => 'Informatique et Technologie', 'description' => 'Matériel informatique, licences logiciels'],
            ['name' => 'Divers',                      'description' => 'Dépenses diverses non catégorisées'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
