<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Poste;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Récupération sécurisée des postes
        $dg = Poste::where('intitule', 'Directeur Général')->firstOrFail();
        $managerPoste = Poste::where('intitule', 'Manager')->firstOrFail();
        $consultantPoste = Poste::where('intitule', 'Consultant')->firstOrFail();
        $developpeurPoste = Poste::where('intitule', 'Développeur')->firstOrFail();

        // ADMIN
        $admin = User::create([
            'nom' => 'Admin',
            'prenom' => 'Super',
            'username' => 'admin',
            'email' => 'rabiatouyantekoua@gmail.com',
            'password' => Hash::make('password'),
            'poste_id' => $dg->id,
            'telephone' => '0123456789',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // RH (attaché au poste Consultant par exemple)
        $rh = User::create([
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'username' => 'marie.rh',
            'email' => 'rh@coftime.com',
            'password' => Hash::make('password'),
            'poste_id' => $consultantPoste->id,
            'telephone' => '0698765432',
            'is_active' => true,
        ]);
        $rh->assignRole('rh');

        // MANAGER
        $manager = User::create([
            'nom' => 'Martin',
            'prenom' => 'Jean',
            'username' => 'jean.manager',
            'email' => 'manager@coftime.com',
            'password' => Hash::make('password'),
            'poste_id' => $managerPoste->id,
            'is_active' => true,
        ]);
        $manager->assignRole('manager');

        // EMPLOYÉS
        $noms = ['Leroy', 'Dubois', 'Moreau', 'Simon', 'Bernard'];
        $prenoms = ['Paul', 'Sophie', 'Lucas', 'Emma', 'Théo'];
        $postesEmployes = [$consultantPoste, $developpeurPoste];

        foreach ($noms as $i => $nom) {
            $user = User::create([
                'nom' => $nom,
                'prenom' => $prenoms[$i],
                'username' => strtolower($prenoms[$i] . '.' . $nom),
                'email' => strtolower($prenoms[$i] . '.' . $nom) . '@coftime.com',
                'password' => Hash::make('password'),
                'poste_id' => collect($postesEmployes)->random()->id,
                'telephone' => '06' . rand(10000000, 99999999),
                'is_active' => true,
            ]);
            $user->assignRole('employe');
        }
    }
}
