<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:voir les rôles')->only('index');
        $this->middleware('permission:créer des rôles')->only(['create', 'store']);
        $this->middleware('permission:modifier des rôles')->only(['edit', 'update']);
        $this->middleware('permission:supprimer des rôles')->only('destroy');
    }

    public function index()
    {
        // On charge les rôles avec leurs permissions
        // ET on ajoute un attribut 'users_custom_count' qui compte via la colonne role_id
        $roles = Role::with('permissions')
            ->addSelect([
                'users_custom_count' => User::selectRaw('count(*)')
                    ->whereColumn('role_id', 'roles.id')
            ])
            ->get();

        return view('pages.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('pages.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'description' => 'nullable|string|max:255'
        ]);

        Role::create($request->all());

        return redirect()->route('admin.roles.index')->with('success', 'Rôle créé avec succès.');
    }

    public function edit(Role $role)
    {
        return view('pages.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255'
        ]);

        $role->update($request->all());

        return redirect()->route('admin.roles.index')->with('success', 'Rôle mis à jour.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return back()->with('success', 'Rôle supprimé.');
    }
}
