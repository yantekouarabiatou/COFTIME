<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === Permissions par module ===
        $permissions = [
            // Utilisateurs
            'voir les utilisateurs',
            'créer des utilisateurs',
            'modifier des utilisateurs',
            'supprimer des utilisateurs',

            // Clients
            'voir les clients',
            'créer des clients',
            'modifier des clients',
            'supprimer des clients',

            // Dossiers
            'voir les dossiers',
            'créer des dossiers',
            'modifier des dossiers',
            'supprimer des dossiers',

            // Gestion des temps
            'voir les entrées journalières',
            'voir tous les temps', // pour admin/RH
            'créer des entrées journalières',
            'modifier des entrées journalières',
            'supprimer des entrées journalières',

            // Congés
            'voir les congés',
            'créer des congés',
            'modifier des congés',
            'supprimer des congés',
            'approuver les congés',

            // Cadeaux & Invitations
            'voir les cadeaux et invitations',
            'créer des cadeaux et invitations',
            'modifier des cadeaux et invitations',
            'supprimer des cadeaux et invitations',

            // Rapports & Exports
            'exporter les temps',
            'voir les rapports mensuels',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // === Rôles ===
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $rh = Role::firstOrCreate(['name' => 'rh']);
        $rh->givePermissionTo([
            'voir tous les temps',
            'voir les utilisateurs',
            'voir les congés',
            'approuver les congés',
            'exporter les temps',
            'voir les rapports mensuels',
            'voir les cadeaux et invitations',
        ]);

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'voir les entrées journalières',
            'créer des entrées journalières',
            'modifier des entrées journalières',
            'voir les congés',
            'créer des congés',
            'voir les dossiers',
            'voir les clients',
        ]);

        $employe = Role::firstOrCreate(['name' => 'employe']);
        $employe->givePermissionTo([
            'créer des entrées journalières',
            'modifier des entrées journalières', // seulement les siennes via Policy
            'créer des congés',
            'voir les cadeaux et invitations',
            'créer des cadeaux et invitations',
        ]);
    }
}
