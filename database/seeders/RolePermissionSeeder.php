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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ================= PERMISSIONS =================
        $permissions = [

            // ================= UTILISATEURS =================
            ['name' => 'voir les utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'créer des utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'modifier des utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'supprimer des utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'assigner des rôles', 'group' => 'utilisateurs'],

            // ================= CONGÉS =================
            ['name' => 'voir les demandes de congés', 'group' => 'conges'],
            ['name' => 'voir toutes les demandes de congés', 'group' => 'conges'],
            ['name' => 'créer des demandes de congés', 'group' => 'conges'],
            ['name' => 'modifier des demandes de congés', 'group' => 'conges'],
            ['name' => 'supprimer des demandes de congés', 'group' => 'conges'],
            ['name' => 'approuver les demandes de congés', 'group' => 'conges'],
            ['name' => 'refuser les demandes de congés', 'group' => 'conges'],
            ['name' => 'valider les demandes de congés', 'group' => 'conges'],

            // ================= SOLDES =================
            ['name' => 'voir les soldes de congés', 'group' => 'conges_soldes'],
            ['name' => 'voir tous les soldes de congés', 'group' => 'conges_soldes'],
            ['name' => 'modifier les soldes de congés', 'group' => 'conges_soldes'],
            ['name' => 'réinitialiser les soldes annuels', 'group' => 'conges_soldes'],
            ['name' => 'gerer les soldes', 'group' => 'conges_soldes'],

            // ================= DASHBOARD =================
            ['name' => 'accéder au dashboard des congés', 'group' => 'dashboard'],
            ['name' => 'voir le calendrier des congés', 'group' => 'dashboard'],
            ['name' => 'voir les statistiques congés', 'group' => 'dashboard'],
            ['name' => 'accéder au tableau de bord admin', 'group' => 'dashboard'],
            ['name' => 'accéder au tableau de bord utilisateur', 'group' => 'dashboard'],

            // ================= ATTESTATIONS =================
            ['name' => 'voir les demandes d attestation', 'group' => 'attestations'],
            ['name' => 'voir toutes les demandes d attestation', 'group' => 'attestations'],
            ['name' => 'créer des demandes d attestation', 'group' => 'attestations'],
            ['name' => 'modifier des demandes d attestation', 'group' => 'attestations'],
            ['name' => 'supprimer des demandes d attestation', 'group' => 'attestations'],
            ['name' => 'approuver les demandes d attestation', 'group' => 'attestations'],
            ['name' => 'refuser les demandes d attestation', 'group' => 'attestations'],
            ['name' => 'valider les demandes d attestation', 'group' => 'attestations'],

            // ================= DEMISSIONS =================
            ['name' => 'soumettre une démission', 'group' => 'demissions'],
            ['name' => 'voir les démissions', 'group' => 'demissions'],
            ['name' => 'approuver les démissions', 'group' => 'demissions'],
            ['name' => 'refuser les démissions', 'group' => 'demissions'],
            ['name' => 'valider les démissions', 'group' => 'demissions'],

            // ================= EXPORTS =================
            ['name' => 'exporter les congés en excel', 'group' => 'exports'],
            ['name' => 'exporter les congés en pdf', 'group' => 'exports'],
            ['name' => 'exporter les soldes de congés', 'group' => 'exports'],

            // ================= PARAMÈTRES =================
            ['name' => 'voir les paramètres', 'group' => 'parametres'],
            ['name' => 'modifier les paramètres', 'group' => 'parametres'],

            // ================= NOTIFICATIONS =================
            ['name' => 'voir les notifications', 'group' => 'notifications'],
            ['name' => 'marquer les notifications comme lues', 'group' => 'notifications'],
            ['name' => 'gérer les notifications', 'group' => 'notifications'],

             // ================= CLIENTS =================
            ['name' => 'voir les clients', 'group' => 'clients'],
            ['name' => 'créer des clients', 'group' => 'clients'],
            ['name' => 'modifier des clients', 'group' => 'clients'],
            ['name' => 'supprimer des clients', 'group' => 'clients'],

            // ================= DOSSIERS =================
            ['name' => 'voir les dossiers', 'group' => 'dossiers'],
            ['name' => 'créer des dossiers', 'group' => 'dossiers'],
            ['name' => 'modifier des dossiers', 'group' => 'dossiers'],
            ['name' => 'supprimer des dossiers', 'group' => 'dossiers'],

            // ================= TEMPS / FEUILLES DE TEMPS =================
            ['name' => 'voir les entrées journalières', 'group' => 'temps'],
            ['name' => 'voir tous les temps', 'group' => 'temps'],
            ['name' => 'créer des entrées journalières', 'group' => 'temps'],
            ['name' => 'modifier des entrées journalières', 'group' => 'temps'],
            ['name' => 'supprimer des entrées journalières', 'group' => 'temps'],
            ['name' => 'valider les feuilles de temps', 'group' => 'temps'],
            ['name' => 'refuser les feuilles de temps', 'group' => 'temps'],

        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                ['group' => $perm['group']]
            );
        }

        // ================= ROLES =================

        // ADMIN
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // RH
        $rh = Role::firstOrCreate(['name' => 'rh']);
        $rh->syncPermissions([
            'voir les utilisateurs',
            'voir les demandes de congés',
            'voir toutes les demandes de congés',
            'créer des demandes de congés',
            'approuver les demandes de congés',
            'refuser les demandes de congés',
            'voir les soldes de congés',
            'modifier les soldes de congés',
            'réinitialiser les soldes annuels',
            'voir les demandes d attestation',
            'approuver les demandes d attestation',
            'refuser les demandes d attestation',
            'voir les démissions',
            'voir les notifications',
            'accéder au tableau de bord utilisateur'
        ]);

        // MANAGER
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'voir les demandes de congés',
            'approuver les demandes de congés',
            'refuser les demandes de congés',
            'voir les soldes de congés',
            'voir les demandes d attestation',
            'approuver les demandes d attestation',
            'refuser les demandes d attestation',
            'voir les notifications',
            'accéder au tableau de bord utilisateur',
            // ================= CLIENTS =================
            'voir les clients',
            'créer des clients',
            'modifier des clients',
            'supprimer des clients',

            //========== DOSSIERS =================
             'voir les dossiers',
             'créer des dossiers',
             'modifier des dossiers',
             'supprimer des dossiers',

            //========== TEMPS / FEUILLES DE TEMPS =================
             'voir les entrées journalières',
             'voir tous les temps',
             'créer des entrées journalières',
             'modifier des entrées journalières',
             'supprimer des entrées journalières',
             'valider les feuilles de temps',
             'refuser les feuilles de temps',
        ]);

        // EMPLOYÉ
        $collaborateur = Role::firstOrCreate(['name' => 'collaborateur']);
        $collaborateur->syncPermissions([
            'créer des demandes de congés',
            'voir les demandes de congés',
            'voir les soldes de congés',
            'créer des demandes d attestation',
            'voir les demandes d attestation',
            'soumettre une démission',
            'voir les notifications',
            'accéder au tableau de bord utilisateur',
            //========== TEMPS / FEUILLES DE TEMPS =================
             'voir les entrées journalières',
             'créer des entrées journalières',
             'modifier des entrées journalières',
             'supprimer des entrées journalières',
        ]);

        // DG
        $dg = Role::firstOrCreate(['name' => 'directeur-general']);

        $permissionsDG = Permission::whereNotIn('group', [
            'utilisateurs',
            'parametres'
        ])->get();

        $dg->syncPermissions($permissionsDG);

        $this->command->info('✅ Seeder nettoyé et optimisé avec succès !');
    }
}