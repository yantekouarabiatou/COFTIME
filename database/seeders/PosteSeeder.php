<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosteSeeder extends Seeder
{
    public function run()
    {
        // Désactiver les contraintes de clé étrangère temporairement
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('postes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $postes = [
            [
                'intitule' => 'Directeur Général',
                'description' => 'Responsable de la direction générale de l\'entreprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'intitule' => 'Administrateur Système',
                'description' => 'Gestion des systèmes informatiques et de la sécurité',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'intitule' => 'Consultant',
                'description' => 'Agent chargé du traitement des plaintes',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'intitule' => 'Développeur',
                'description' => 'Responsable des relations clients',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'intitule' => 'Manager',
                'description' => 'Technicien spécialisé',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('postes')->insert($postes);
    }
}
