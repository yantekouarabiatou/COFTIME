<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Réinitialiser le cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Nettoyage des tables de permissions
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ==================== 1. CRÉATION DES PERMISSIONS ====================
        $permissions = [
            // Utilisateurs
            ['name' => 'voir les utilisateurs', 'group' => 'utilisateurs', 'description' => 'Voir la liste des utilisateurs'],
            ['name' => 'créer des utilisateurs', 'group' => 'utilisateurs', 'description' => 'Créer un nouvel utilisateur'],
            ['name' => 'modifier les utilisateurs', 'group' => 'utilisateurs', 'description' => 'Modifier un utilisateur'],
            ['name' => 'supprimer les utilisateurs', 'group' => 'utilisateurs', 'description' => 'Supprimer un utilisateur'],
            ['name' => 'assigner des rôles', 'group' => 'utilisateurs', 'description' => 'Assigner des rôles aux utilisateurs'],

            // Rôles & Permissions
            ['name' => 'voir les rôles', 'group' => 'rôles', 'description' => 'Voir la liste des rôles'],
            ['name' => 'créer des rôles', 'group' => 'rôles', 'description' => 'Créer un nouveau rôle'],
            ['name' => 'modifier des rôles', 'group' => 'rôles', 'description' => 'Modifier un rôle'],
            ['name' => 'supprimer des rôles', 'group' => 'rôles', 'description' => 'Supprimer un rôle'],
            ['name' => 'gérer les permissions', 'group' => 'rôles', 'description' => 'Attribuer des permissions aux rôles'],
            ['name' => 'gérer les permissions des rôles', 'group' => 'rôles_permissions', 'description' => 'Attribuer/retirer des permissions à un rôle'],
            ['name' => 'voir les permissions', 'group' => 'rôles_permissions', 'description' => 'Voir la liste complète des permissions'],

            // Plaintes
            ['name' => 'voir les plaintes', 'group' => 'plaintes', 'description' => 'Voir ses plaintes ou toutes selon le rôle'],
            ['name' => 'créer des plaintes', 'group' => 'plaintes', 'description' => 'Créer une nouvelle plainte'],
            ['name' => 'modifier des plaintes', 'group' => 'plaintes', 'description' => 'Modifier une plainte'],
            ['name' => 'supprimer des plaintes', 'group' => 'plaintes', 'description' => 'Supprimer une plainte'],
            ['name' => 'valider des plaintes', 'group' => 'plaintes', 'description' => 'Valider ou clôturer une plainte'],
            ['name' => 'assigner des plaintes', 'group' => 'plaintes', 'description' => 'Assigner une plainte à un agent'],
            ['name' => 'voir toutes les plaintes', 'group' => 'plaintes', 'description' => 'Accès à toutes les plaintes du système'],

            // Clients Audit
            ['name' => 'voir les clients audit', 'group' => 'clients_audit', 'description' => 'Voir la liste des clients audit'],
            ['name' => 'créer des clients audit', 'group' => 'clients_audit', 'description' => 'Ajouter un nouveau client audit'],
            ['name' => 'modifier des clients audit', 'group' => 'clients_audit', 'description' => 'Modifier un client audit'],
            ['name' => 'supprimer des clients audit', 'group' => 'clients_audit', 'description' => 'Supprimer un client audit'],

            // Cadeaux & Invitations
            ['name' => 'voir les cadeaux et invitations', 'group' => 'cadeaux_invitations', 'description' => 'Voir les déclarations de cadeaux/hospitalités'],
            ['name' => 'créer des cadeaux et invitations', 'group' => 'cadeaux_invitations', 'description' => 'Déclarer un cadeau ou une invitation'],
            ['name' => 'modifier des cadeaux et invitations', 'group' => 'cadeaux_invitations', 'description' => 'Modifier une déclaration'],
            ['name' => 'supprimer des cadeaux et invitations', 'group' => 'cadeaux_invitations', 'description' => 'Supprimer une déclaration'],

            // Déclarations d'Indépendance
            ['name' => 'voir les indépendances', 'group' => 'independances', 'description' => 'Voir les déclarations d\'indépendance'],
            ['name' => 'créer des indépendances', 'group' => 'independances', 'description' => 'Créer une déclaration d\'indépendance'],
            ['name' => 'modifier des indépendances', 'group' => 'independances', 'description' => 'Modifier une déclaration'],
            ['name' => 'supprimer des indépendances', 'group' => 'independances', 'description' => 'Supprimer une déclaration'],

            // Assignations
            ['name' => 'voir les assignations', 'group' => 'assignations', 'description' => 'Voir les assignations de missions'],
            ['name' => 'créer des assignations', 'group' => 'assignations', 'description' => 'Assigner une mission à un collaborateur'],
            ['name' => 'modifier des assignations', 'group' => 'assignations', 'description' => 'Modifier une assignation'],
            ['name' => 'supprimer des assignations', 'group' => 'assignations', 'description' => 'Supprimer une assignation'],

            // Postes
            ['name' => 'voir les postes', 'group' => 'postes', 'description' => 'Voir la liste des postes'],
            ['name' => 'créer des postes', 'group' => 'postes', 'description' => 'Créer un nouveau poste'],
            ['name' => 'modifier des postes', 'group' => 'postes', 'description' => 'Modifier un poste'],
            ['name' => 'supprimer des postes', 'group' => 'postes', 'description' => 'Supprimer un poste'],

            // Conflits d'intérêts
            ['name' => 'voir les conflits d\'intérêts', 'group' => 'interets', 'description' => 'Voir les déclarations de conflits d\'intérêts'],
            ['name' => 'créer des conflits d\'intérêts', 'group' => 'interets', 'description' => 'Déclarer un conflit d\'intérêts'],
            ['name' => 'modifier des conflits d\'intérêts', 'group' => 'interets', 'description' => 'Modifier une déclaration'],
            ['name' => 'supprimer des conflits d\'intérêts', 'group' => 'interets', 'description' => 'Supprimer une déclaration'],

            // Statistiques & Rapports
            ['name' => 'voir les statistiques', 'group' => 'statistiques', 'description' => 'Accéder aux tableaux de bord et statistiques'],
            ['name' => 'exporter les données', 'group' => 'statistiques', 'description' => 'Exporter les données (Excel, PDF, etc.)'],

            // Paramètres système
            ['name' => 'voir les paramètres', 'group' => 'paramètres', 'description' => 'Accéder aux paramètres du système'],
            ['name' => 'modifier les paramètres', 'group' => 'paramètres', 'description' => 'Modifier les paramètres généraux'],

            // Médias & Documents
            ['name' => 'voir les documents', 'group' => 'médias', 'description' => 'Voir les documents joints'],
            ['name' => 'télécharger les documents', 'group' => 'médias', 'description' => 'Télécharger les pièces jointes'],
            ['name' => 'supprimer les documents', 'group' => 'médias', 'description' => 'Supprimer un document'],

            // Tableaux de bord
            ['name' => 'accéder au tableau de bord admin', 'group' => 'dashboard', 'description' => 'Accès complet au tableau de bord administrateur'],
            ['name' => 'accéder au tableau de bord utilisateur', 'group' => 'dashboard', 'description' => 'Accès au tableau de bord personnel'],
        ];

        foreach ($permissions as $perm) {
            Permission::create([
                'name' => $perm['name'],
                'guard_name' => 'web',
                'group' => $perm['group'],
                'description' => $perm['description']
            ]);
        }

        // ==================== 2. CRÉATION DES RÔLES ====================

        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web', 'description' => 'Accès total']);
        $superAdminRole->givePermissionTo(Permission::all());

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web', 'description' => 'Administrateur']);
        $adminRole->givePermissionTo(Permission::all());

        $responsableConformiteRole = Role::create(['name' => 'responsable-conformite', 'guard_name' => 'web', 'description' => 'Responsable de la conformité']);
        $responsableConformiteRole->givePermissionTo([
            'voir les plaintes', 'créer des plaintes', 'modifier des plaintes', 'valider des plaintes', 'assigner des plaintes', 'voir toutes les plaintes',
            'voir les statistiques', 'accéder au tableau de bord admin', 'exporter les données',
            'voir les conflits d\'intérêts', 'créer des conflits d\'intérêts', 'modifier des conflits d\'intérêts',
            'voir les cadeaux et invitations', 'créer des cadeaux et invitations', 'modifier des cadeaux et invitations',
            'voir les indépendances', 'créer des indépendances', 'modifier des indépendances',
            'voir les rôles', 'voir les permissions'
        ]);

        $auditeurRole = Role::create(['name' => 'auditeur', 'guard_name' => 'web', 'description' => 'Auditeur interne']);
        $auditeurRole->givePermissionTo([
            'voir les plaintes', 'créer des plaintes', 'modifier des plaintes',
            'voir les clients audit', 'créer des clients audit', 'modifier des clients audit',
            'voir les statistiques', 'accéder au tableau de bord utilisateur', 'exporter les données',
            'voir les assignations', 'créer des assignations'
        ]);

        $gestionnairePlaintesRole = Role::create(['name' => 'gestionnaire-plaintes', 'guard_name' => 'web', 'description' => 'Gestionnaire des plaintes']);
        $gestionnairePlaintesRole->givePermissionTo([
            'voir les plaintes', 'créer des plaintes', 'modifier des plaintes', 'valider des plaintes', 'assigner des plaintes', 'voir toutes les plaintes',
            'voir les statistiques', 'accéder au tableau de bord admin', 'exporter les données'
        ]);

        $agentRole = Role::create(['name' => 'agent', 'guard_name' => 'web', 'description' => 'Agent de traitement']);
        $agentRole->givePermissionTo(['voir les plaintes', 'modifier des plaintes', 'accéder au tableau de bord utilisateur']);

        $userRole = Role::create(['name' => 'user', 'guard_name' => 'web', 'description' => 'Utilisateur standard']);
        $userRole->givePermissionTo([
            'voir les plaintes', 'créer des plaintes',
            'voir les conflits d\'intérêts', 'créer des conflits d\'intérêts',
            'voir les cadeaux et invitations', 'créer des cadeaux et invitations',
            'voir les indépendances', 'créer des indépendances',
            'accéder au tableau de bord utilisateur'
        ]);

        $this->command->info('Rôles et Permissions créés avec succès !');
    }
}
