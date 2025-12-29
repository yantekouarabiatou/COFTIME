<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * Récupère toutes les permissions et les groupe par le champ 'group'.
     */
    public function getGroupedPermissions(): Collection
    {
        return Permission::all()->groupBy('group');
    }

    /**
     * Définit les libellés (labels) des groupes pour l'affichage.
     */
    public function getPermissionGroups(): array
    {
        return [
            'utilisateurs'          => 'Gestion des Utilisateurs',
            'rôles'                 => 'Rôles et Permissions (Système)',
            'plaintes'              => 'Gestion des Plaintes',
            'clients_audit'         => 'Clients Audit',
            'cadeaux_invitations'   => 'Cadeaux et Invitations',
            'independances'         => 'Déclarations d\'Indépendance',
            'assignations'          => 'Assignations de Missions',
            'postes'                => 'Gestion des Postes',
            'interets'              => 'Conflits d\'Intérêts',
            'statistiques'          => 'Statistiques et Rapports',
            'paramètres'            => 'Paramètres Système',
            'médias'                => 'Médias et Documents',
            'dashboard'             => 'Tableaux de Bord',
            'rôles_permissions'     => 'Gestion des Permissions Méta'
        ];
    }

    /**
     * Synchronise les permissions d'un rôle.
     * @param Role $role L'instance du rôle Spatie
     * @param array $permissionNames Tableau de noms de permissions (ex: ['voir les plaintes', 'créer des plaintes'])
     */
    public function syncRolePermissions(Role $role, array $permissionNames): Role
    {
        // Spatie syncPermissions accepte directement un tableau de noms de permissions
        $role->syncPermissions($permissionNames);

        return $role;
    }

    /**
     * Crée un rôle et lui assigne immédiatement des permissions.
     * @param array $roleData Données du rôle (ex: ['name' => 'nouveau-role', 'guard_name' => 'web'])
     * @param array $permissionNames Tableau de noms de permissions
     */
    public function createRoleWithPermissions(array $roleData, array $permissionNames = []): Role
    {
        $role = Role::create($roleData);

        if (!empty($permissionNames)) {
            $this->syncRolePermissions($role, $permissionNames);
        }

        return $role;
    }
}