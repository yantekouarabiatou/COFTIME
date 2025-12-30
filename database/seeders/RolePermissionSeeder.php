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
        // Reset cache des permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === LISTE COMPLÈTE DES PERMISSIONS ===
        $permissions = [

            // ================= UTILISATEURS =================
            'voir les utilisateurs',
            'créer des utilisateurs',
            'modifier les utilisateurs',
            'supprimer les utilisateurs',
            'assigner des rôles',

            // ================= CLIENTS =================
            'voir les clients',
            'créer des clients',
            'modifier des clients',
            'supprimer des clients',

            // ================= DOSSIERS =================
            'voir les dossiers',
            'créer des dossiers',
            'modifier des dossiers',
            'supprimer des dossiers',

            // ================= TEMPS =================
            'voir les entrées journalières',
            'voir tous les temps',
            'créer des entrées journalières',
            'modifier des entrées journalières',
            'supprimer des entrées journalières',

            // ================= CONGÉS =================
            'voir les congés',
            'créer des congés',
            'modifier des congés',
            'supprimer des congés',
            'approuver les congés',

            // ================= STATISTIQUES / EXPORT =================
            'voir les statistiques',
            'exporter les données',
            'exporter les temps',
            'voir les rapports mensuels',

            // ================= PARAMÈTRES =================
            'voir les paramètres',
            'modifier les paramètres',

            // ================= DOCUMENTS =================
            'voir les documents',
            'télécharger les documents',
            'supprimer les documents',

            // ================= TABLEAUX DE BORD =================
            'accéder au tableau de bord admin',
            'accéder au tableau de bord utilisateur',

            // ================= POSTES =================
            'voir les postes',
            'créer des postes',
            'modifier des postes',
            'supprimer des postes',

            // ================= RÔLES & PERMISSIONS =================
            'voir les rôles',
            'créer des rôles',
            'modifier des rôles',
            'supprimer des rôles',
            'gérer les permissions',
            'voir les permissions',

            // ================= LOGS =================
            'voir les logs',
            'voir les logs système',
            'supprimer les logs',
            'exporter les logs',

            // ================= NOTIFICATIONS =================
            'voir les notifications',
            'marquer les notifications comme lues',
            'supprimer les notifications',
            'envoyer des notifications',
            'gérer les notifications',

            // ================= AUTRES =================
            'voir les cadeaux et invitations',
            'créer des cadeaux et invitations',
        ];

        // Création des permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ================= RÔLES =================

        // ADMIN → accès total
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // RH
        $rh = Role::firstOrCreate(['name' => 'rh']);
        $rh->syncPermissions([
            'voir tous les temps',
            'voir les utilisateurs',
            'voir les congés',
            'approuver les congés',
            'exporter les temps',
            'voir les rapports mensuels',
            'voir les notifications',
            'marquer les notifications comme lues',
        ]);

        // MANAGER
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'voir les entrées journalières',
            'créer des entrées journalières',
            'modifier des entrées journalières',
            'voir les congés',
            'créer des congés',
            'voir les dossiers',
            'voir les clients',
            'voir les notifications',
            'marquer les notifications comme lues',
        ]);

        // EMPLOYÉ
        $employe = Role::firstOrCreate(['name' => 'employe']);
        $employe->syncPermissions([
            'créer des entrées journalières',
            'modifier des entrées journalières',
            'créer des congés',
            'voir les notifications',
            'marquer les notifications comme lues',
        ]);
    }
}
