<?php

namespace App\Http\Controllers;

use App\Services\PermissionService;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
        $this->middleware('auth');

        // Permissions
        $this->middleware('permission:voir les permissions')->only(['index']);
        // On utilise 'gérer les permissions' pour l'affichage (show) ET la modification (update)
        $this->middleware('permission:gérer les permissions')->only(['show', 'updateRolePermissions']);
    }

    // 1. Liste des rôles (Tableau de bord)
    public function index()
    {
        $groupedPermissions = $this->permissionService->getGroupedPermissions();
        $groupLabels        = $this->permissionService->getPermissionGroups();

        // Récupérer tous les rôles avec permissions et le nombre d’utilisateurs
        $roles = Role::with('permissions')
            ->withCount(['users as users_custom_count' => function ($query) {
                $query->where('model_type', User::class); // Spatie pivot
            }])
            ->get();

        return view('pages.permissions.index', compact('groupedPermissions', 'groupLabels', 'roles'));
    }

    // 2. Affiche le formulaire de gestion (Fusion de Show et Edit)
    public function show(Role $role)
    {
        // On charge les permissions actuelles du rôle pour cocher les cases
        $role->load('permissions');

        $groupedPermissions = $this->permissionService->getGroupedPermissions();
        $groupLabels        = $this->permissionService->getPermissionGroups();

        // On retourne la vue qui contient le formulaire
        return view('pages.permissions.show', compact(
            'role',
            'groupedPermissions',
            'groupLabels'
        ));
    }

    // 3. Traite le formulaire (Action du formulaire)
    public function updateRolePermissions(Request $request, Role $role)
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Impossible de modifier les permissions du Super Admin.');
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        // Utilisation du service pour synchroniser
        $this->permissionService->syncRolePermissions($role, $request->permissions ?? []);

        Alert::success('Succès', "Permissions mises à jour pour le rôle : {$role->name}");

        // On redirige vers l'index, ou on peut rester sur la page avec back()
        return redirect()->route('admin.roles.permissions.index');
    }
}
