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
        // ===== ADMIN =====
        $admin = User::updateOrCreate(
            ['username' => 'admin'], // clé unique
            [
                'nom' => 'Admin',
                'prenom' => 'Super',
                'email' => 'rabiatouyantekoua@gmail.com',
                'password' => Hash::make('password'),
                'poste_id' => Poste::where('intitule', 'Directeur Général')->first()?->id,
                'telephone' => '0123456789',
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin']);

        // ===== RH =====
        $rh = User::updateOrCreate(
            ['username' => 'marie.rh'],
            [
                'nom' => 'Dupont',
                'prenom' => 'Marie',
                'email' => 'rh@coftime.com',
                'password' => Hash::make('password'),
                'poste_id' => Poste::where('intitule', 'Responsable RH')->first()?->id,
                'telephone' => '0698765432',
                'is_active' => true,
            ]
        );
        $rh->syncRoles(['rh']);

        // ===== MANAGER =====
        $manager = User::updateOrCreate(
            ['username' => 'jean.manager'],
            [
                'nom' => 'Martin',
                'prenom' => 'Jean',
                'email' => 'manager@coftime.com',
                'password' => Hash::make('password'),
                'poste_id' => Poste::where('intitule', 'Manager')->first()?->id,
                'is_active' => true,
            ]
        );
        $manager->syncRoles(['manager']);
    }
}
