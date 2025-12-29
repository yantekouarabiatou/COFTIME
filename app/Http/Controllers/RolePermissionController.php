<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PermissionService;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;

        // Application des middlewares
        $this->middleware('auth');

        // Utilisez des noms simples qui correspondent à vos permissions
        $this->middleware('permission:edit roles')->only(['edit', 'update']);
        $this->middleware('permission:view roles')->only(['index']);
        $this->middleware('permission:create roles')->only(['create', 'store']);
        $this->middleware('permission:delete roles')->only(['destroy']);
    }

    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $groupedPermissions = $this->permissionService->getGroupedPermissions();
        $groupLabels        = $this->permissionService->getPermissionGroups();

        // MODIFICATION ICI : On ajoute le compte personnalisé
        $roles = Role::with('permissions')
            ->addSelect([
                'users_custom_count' => User::selectRaw('count(*)')
                    ->whereColumn('role_id', 'roles.id')
            ])
            ->get();

        return view('pages.permissions.index', compact(
            'groupedPermissions',
            'groupLabels',
            'roles'
        ));
    }
    public function create()
    { 
        return view('pages.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
        ]);

        $role = Role::create([
            'name'        => $request->name,
            'description' => $request->description,
            'guard_name'  => 'web',
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Rôle « {$role->name} » créé avec succès !");
    }

    public function edit(Role $role)
    {
        // Empêcher la modification des rôles système
        if (in_array($role->name, ['super-admin', 'admin'])) {
            return redirect()->route('admin.roles.index')
                ->with('warning', 'Ce rôle système ne peut pas être modifié.');
        }

        $groupedPermissions = $this->permissionService->getGroupedPermissions();
        $groupLabels        = $this->permissionService->getPermissionGroups();
        $rolePermissions    = $role->permissions->pluck('name')->toArray();

        return view('pages.roles.edit', compact(
            'role',
            'groupedPermissions',
            'groupLabels',
            'rolePermissions'
        ));
    }

    
    public function update(Request $request, Role $role)
    {
        // 1. Protection des rôles système
        if (in_array($role->name, ['super-admin', 'admin'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Les rôles système ne peuvent pas être modifiés.');
        }

        // 2. Validation
        $request->validate([
            'name'          => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description'   => 'nullable|string|max:500',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name', // On valide que le nom technique existe
        ], [
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.unique'   => 'Ce nom de rôle est déjà utilisé.',
            'permissions.*.exists' => 'Une des permissions sélectionnées est invalide.'
        ]);

        try {
            // 3. Mise à jour des attributs du rôle
            $role->update([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            // 4. Synchronisation des permissions
            // Si aucune case n'est cochée, $request->permissions sera null, d'où le ?? []
            $permissions = $request->permissions ?? [];
            $this->permissionService->syncRolePermissions($role, $permissions);

            return redirect()
                ->route('admin.roles.index')
                ->with('success', "Le rôle « {$role->name} » et ses permissions ont été mis à jour.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Une erreur est survenue lors de la mise à jour : " . $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        // Empêcher la suppression des rôles système
        if (in_array($role->name, ['super-admin', 'admin'])) {
            return back()->with('error', 'Les rôles système ne peuvent pas être supprimés.');
        }

        // Vérifier si le rôle est utilisé
        if ($role->users()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer ce rôle car il est attribué à ' . $role->users()->count() . ' utilisateur(s).'
            );
        }

        $role->delete();

        return back()->with('success', 'Rôle supprimé avec succès.');
    }
}
