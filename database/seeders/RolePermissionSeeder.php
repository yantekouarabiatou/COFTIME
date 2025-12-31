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
            ['name' => 'voir les utilisateurs', 'group' => 'utilisateurs', 'description' => 'Voir la liste des utilisateurs'],
            ['name' => 'créer des utilisateurs', 'group' => 'utilisateurs', 'description' => 'Créer un nouvel utilisateur'],
            ['name' => 'modifier des utilisateurs', 'group' => 'utilisateurs', 'description' => 'Modifier un utilisateur'],
            ['name' => 'supprimer des utilisateurs', 'group' => 'utilisateurs', 'description' => 'Supprimer un utilisateur'],
            ['name' => 'assigner des rôles', 'group' => 'utilisateurs', 'description' => 'Assigner des rôles aux utilisateurs'],

            // ================= CLIENTS =================
            ['name' => 'voir les clients', 'group' => 'Clients', 'description' => 'Voir la liste des clients'],
            ['name' => 'créer des clients', 'group' => 'Clients', 'description' => 'Créer un nouveau client'],
            ['name' => 'modifier des clients', 'group' => 'Clients', 'description' => 'Modifier un client'],
            ['name' => 'supprimer des clients', 'group' => 'Clients', 'description' => 'Supprimer un client'],

            // ================= DOSSIERS =================
            ['name' => 'voir les dossiers', 'group' => 'Dossiers', 'description' => 'Voir la liste des dossiers'],
            ['name' => 'créer des dossiers', 'group' => 'Dossiers', 'description' => 'Créer un nouveau dossier'],
            ['name' => 'modifier des dossiers', 'group' => 'Dossiers', 'description' => 'Modifier un dossier'],
            ['name' => 'supprimer des dossiers', 'group' => 'Dossiers', 'description' => 'Supprimer un dossier'],

            // ================= TEMPS =================
            ['name' => 'voir les entrées journalières', 'group' => 'Temps', 'description' => 'Voir les entrées journalières'],
            ['name' => 'voir tous les temps', 'group' => 'Temps', 'description' => 'Voir toutes les entrées temps'],
            ['name' => 'créer des entrées journalières', 'group' => 'Temps', 'description' => 'Créer une nouvelle entrée journalière'],
            ['name' => 'modifier des entrées journalières', 'group' => 'Temps', 'description' => 'Modifier une entrée journalière'],
            ['name' => 'supprimer des entrées journalières', 'group' => 'Temps', 'description' => 'Supprimer une entrée journalière'],

            // ================= CONGÉS =================
            ['name' => 'voir les congés', 'group' => 'Congés', 'description' => 'Voir la liste des congés'],
            ['name' => 'créer des congés', 'group' => 'Congés', 'description' => 'Créer un nouveau congé'],
            ['name' => 'modifier des congés', 'group' => 'Congés', 'description' => 'Modifier un congé'],
            ['name' => 'supprimer des congés', 'group' => 'Congés', 'description' => 'Supprimer un congé'],
            ['name' => 'approuver les congés', 'group' => 'Congés', 'description' => 'Approuver ou refuser un congé'],

            // ================= STATISTIQUES / EXPORT =================
            ['name' => 'voir les statistiques', 'group' => 'statistiques', 'description' => 'Voir les statistiques'],
            ['name' => 'exporter les données', 'group' => 'statistiques', 'description' => 'Exporter les données du système'],
            ['name' => 'exporter les temps', 'group' => 'statistiques', 'description' => 'Exporter les temps enregistrés'],
            ['name' => 'voir les rapports mensuels', 'group' => 'statistiques', 'description' => 'Voir les rapports mensuels'],

            // ================= PARAMÈTRES =================
            ['name' => 'voir les paramètres', 'group' => 'paramètres', 'description' => 'Voir les paramètres du système'],
            ['name' => 'modifier les paramètres', 'group' => 'paramètres', 'description' => 'Modifier les paramètres du système'],

            // ================= DOCUMENTS =================
            ['name' => 'voir les documents', 'group' => 'médias', 'description' => 'Voir les documents et médias'],
            ['name' => 'télécharger les documents', 'group' => 'médias', 'description' => 'Télécharger les documents'],
            ['name' => 'supprimer les documents', 'group' => 'médias', 'description' => 'Supprimer les documents'],

            // ================= TABLEAUX DE BORD =================
            ['name' => 'accéder au tableau de bord admin', 'group' => 'dashboard', 'description' => 'Accéder au tableau de bord admin'],
            ['name' => 'accéder au tableau de bord utilisateur', 'group' => 'dashboard', 'description' => 'Accéder au tableau de bord utilisateur'],

            // ================= POSTES =================
            ['name' => 'voir les postes', 'group' => 'postes', 'description' => 'Voir les postes'],
            ['name' => 'créer des postes', 'group' => 'postes', 'description' => 'Créer un nouveau poste'],
            ['name' => 'modifier des postes', 'group' => 'postes', 'description' => 'Modifier un poste'],
            ['name' => 'supprimer des postes', 'group' => 'postes', 'description' => 'Supprimer un poste'],

            // ================= RÔLES & PERMISSIONS =================
            ['name' => 'voir les rôles', 'group' => 'rôles', 'description' => 'Voir la liste des rôles'],
            ['name' => 'créer des rôles', 'group' => 'rôles', 'description' => 'Créer un nouveau rôle'],
            ['name' => 'modifier des rôles', 'group' => 'rôles', 'description' => 'Modifier un rôle'],
            ['name' => 'supprimer des rôles', 'group' => 'rôles', 'description' => 'Supprimer un rôle'],
            ['name' => 'gérer les permissions', 'group' => 'rôles', 'description' => 'Attribuer des permissions aux rôles'],
            ['name' => 'voir les permissions', 'group' => 'rôles_permissions', 'description' => 'Voir la liste complète des permissions'],

            // ================= LOGS =================
            ['name' => 'voir les logs', 'group' => 'Logs', 'description' => 'Voir les logs du système'],
            ['name' => 'voir les logs système', 'group' => 'Logs', 'description' => 'Voir les logs système détaillés'],
            ['name' => 'supprimer les logs', 'group' => 'Logs', 'description' => 'Supprimer les logs'],
            ['name' => 'exporter les logs', 'group' => 'Logs', 'description' => 'Exporter les logs'],

            // ================= NOTIFICATIONS =================
            ['name' => 'voir les notifications', 'group' => 'notifications', 'description' => 'Voir les notifications'],
            ['name' => 'marquer les notifications comme lues', 'group' => 'notifications', 'description' => 'Marquer comme lues'],
            ['name' => 'supprimer les notifications', 'group' => 'notifications', 'description' => 'Supprimer les notifications'],
            ['name' => 'envoyer des notifications', 'group' => 'notifications', 'description' => 'Envoyer des notifications'],
            ['name' => 'gérer les notifications', 'group' => 'notifications', 'description' => 'Gérer les notifications'],
        ];


        // Création des permissions
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['group' => $permission['group'], 'description' => $permission['description']]
            );
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
