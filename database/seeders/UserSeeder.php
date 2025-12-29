<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Nettoyage de la table users
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 2. Récupération des objets Rôles (pour avoir leurs IDs)
        $superAdminRole = Role::where('name', 'super-admin')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $complianceRole = Role::where('name', 'responsable-conformite')->firstOrFail();
        $auditeurRole = Role::where('name', 'auditeur')->firstOrFail();
        $gestionnaireRole = Role::where('name', 'gestionnaire-plaintes')->firstOrFail();
        $agentRole = Role::where('name', 'agent')->firstOrFail();
        $userRole = Role::where('name', 'user')->firstOrFail();

        // ==================== UTILISATEURS SPÉCIFIQUES ====================

        // 1. Super Administrateur
        $superAdmin = User::create([
            'nom' => 'ADMIN',
            'prenom' => 'Super',
            'username' => 'superadmin',
            'email' => 'rabiatouyantekoua@gmail.com',
            'password' => Hash::make('password'),
            'photo' => 'default.png',
            'email_verified_at' => now(),
            'is_active' => true,
            'poste_id' => 4,
            'role_id' => $superAdminRole->id, // <--- Remplissage manuel
        ]);
        // Indispensable pour que Spatie fonctionne
        $superAdmin->assignRole('super-admin');

        // 2. Administrateur
        $admin = User::create([
            'nom' => 'IROKO',
            'prenom' => 'Belvik',
            'username' => 'admin',
            'email' => 'admin@cofima.com',
            'password' => Hash::make('password'),
            'photo' => 'default.png',
            'email_verified_at' => now(),
            'is_active' => true,
            'poste_id' => 2,
            'role_id' => $adminRole->id, // <--- Remplissage manuel
        ]);
        $admin->assignRole('admin');

        // 3. Responsable Conformité
        $compliance = User::create([
            'nom' => 'Responsable',
            'prenom' => 'Conformité',
            'username' => 'compliance',
            'email' => 'compliance@system.com',
            'password' => Hash::make('password'),
            'photo' => 'default.png',
            'email_verified_at' => now(),
            'is_active' => true,
            'poste_id' => 7,
            'role_id' => $complianceRole->id, // <--- Remplissage manuel
        ]);
        $compliance->assignRole('responsable-conformite');

        // 4. Auditeur
        $auditeur = User::create([
            'nom' => 'Auditeur',
            'prenom' => 'Interne',
            'username' => 'auditeur',
            'email' => 'auditeur@system.com',
            'password' => Hash::make('password'),
            'photo' => 'default.png',
            'email_verified_at' => now(),
            'is_active' => true,
            'poste_id' => 5,
            'role_id' => $auditeurRole->id, // <--- Remplissage manuel
        ]);
        $auditeur->assignRole('auditeur');

        // 5. Gestionnaire de Plaintes
        $gestionnaire = User::create([
            'nom' => 'Gestionnaire',
            'prenom' => 'Plaintes',
            'username' => 'gestionnaire',
            'email' => 'gestionnaire@system.com',
            'password' => Hash::make('password'),
            'photo' => 'default.png',
            'email_verified_at' => now(),
            'is_active' => true,
            'poste_id' => 3,
            'role_id' => $gestionnaireRole->id, // <--- Remplissage manuel
        ]);
        $gestionnaire->assignRole('gestionnaire-plaintes');

        // 6. Agent
        $agent = User::create([
            'nom' => 'Agent',
            'prenom' => 'Traitement',
            'username' => 'agent',
            'email' => 'agent@system.com',
            'password' => Hash::make('password'),
            'photo' => 'default.png',
            'email_verified_at' => now(),
            'is_active' => true,
            'poste_id' => 4,
            'role_id' => $agentRole->id, // <--- Remplissage manuel
        ]);
        $agent->assignRole('agent');

        // 7. Utilisateur Standard
        $user = User::create([
            'nom' => 'Utilisateur',
            'prenom' => 'Standard',
            'username' => 'user',
            'email' => 'user@system.com',
            'password' => Hash::make('password'),
            'photo' => 'default.png',
            'email_verified_at' => now(),
            'is_active' => true,
            'poste_id' => 3,
            'role_id' => $userRole->id, // <--- Remplissage manuel
        ]);
        $user->assignRole($userRole);

        // 8. Utilisateur inactif (Test)
        $userInactif = User::create([
            'nom' => 'DUBOIS',
            'prenom' => 'Jean',
            'username' => 'jdubois',
            'email' => 'inactif@cofima.com',
            'password' => Hash::make('password'),
            'photo' => 'default.png',
            'email_verified_at' => now(),
            'is_active' => false,
            'poste_id' => 5,
            'role_id' => $userRole->id, // <--- Remplissage manuel
        ]);
        $userInactif->assignRole('user');

        // ==================== UTILISATEURS FACTICES (FAKER) ====================

        // On crée quelques utilisateurs supplémentaires pour peupler la base
        // On utilise use() pour passer les objets rôles dans la fonction anonyme
        User::factory(10)->make()->each(function ($u) use ($userRole, $agentRole) {

            // Choisir un rôle aléatoire (Objet Role)
            $randomRole = rand(0, 1) ? $userRole : $agentRole;

            // Assigner l'ID au modèle avant de sauvegarder
            $u->role_id = $randomRole->id;
            $u->save();

            // Assigner le rôle Spatie
            $u->assignRole($randomRole);
        });

        $this->command->info('Utilisateurs créés avec role_id rempli avec succès !');
    }
}
