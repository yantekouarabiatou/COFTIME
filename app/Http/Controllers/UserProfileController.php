<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Poste;
use App\Models\Plainte;
use App\Models\ClientAudit;
use App\Models\CadeauInvitation;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;

class UserProfileController extends Controller
{
        public function editUser($id)
    {
        $user = Auth::user();

        // Charger les relations nécessaires
        // NE chargez PAS 'role' si vous avez des problèmes
        $user->load([
            'poste',
            'creator',
            'plaintes' => function($query) {
                $query->latest()->limit(5);
            },
            'clientAudits' => function($query) {
                $query->latest()->limit(5);
            },
            'cadeauInvitations' => function($query) {
                $query->latest()->limit(5);
            },
            'independances' => function($query) {
                $query->latest()->limit(5);
            },
            'interets' => function($query) {
                $query->latest()->limit(5);
            }
        ]);

        // Récupérer la liste des postes pour le formulaire
        $postes = Poste::orderBy('intitule')
                    ->get();

        // Récupérer les statistiques
        $statistiques = [
            'total_plaintes' => $user->plaintes()->count(),
            'total_audits' => $user->clientAudits()->count(),
            'total_cadeaux' => $user->cadeauInvitations()->count(),
            'total_interets' => $user->interets()->count(),
            'total_independances' => $user->independances()->count(),
        ];

        return view('profile.edit', compact('user', 'postes', 'statistiques'));
    }

    /**
     * Afficher le profil d'un autre utilisateur (pour admin)
     */
    public function showUser($id)
    {
        // Vérifier les permissions
        if (!Auth::user()->hasRole('admin') && Auth::id() != $id) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::with([
            'poste',
            'role',
            'creator',
            'plaintes' => function($query) {
                $query->latest()->limit(5);
            },
            'clientAudits' => function($query) {
                $query->latest()->limit(5);
            },
            'cadeauInvitations' => function($query) {
                $query->latest()->limit(5);
            }
        ])->findOrFail($id);

            // Récupérer la liste des postes pour le formulaire
        $postes = Poste::orderBy('intitule')
                    ->get();

        $statistiques = [
            'total_plaintes' => $user->plaintes()->count(),
            'total_audits' => $user->clientAudits()->count(),
            'total_cadeaux' => $user->cadeauInvitations()->count(),
            'total_interets' => $user->interets()->count(),
            'total_independances' => $user->independances()->count(),
        ];

        return view('profile.show', compact('user', 'postes', 'statistiques'));
    }

    /**
     * Changer le mot de passe
     */

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => [
                'required',
                'current_password' // Cette règle vérifie que le mot de passe actuel est correct
            ],
            'new_password' => [
                'required',
                'confirmed',
                'different:current_password', // Le nouveau mot de passe doit être différent de l'ancien
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(), // Vérifie que le mot de passe n'est pas compromis
            ],
        ], [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'new_password.required' => 'Le nouveau mot de passe est requis.',
            'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'new_password.different' => 'Le nouveau mot de passe doit être différent de l\'actuel.',
            'new_password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'new_password.mixed' => 'Le mot de passe doit contenir des majuscules et des minuscules.',
            'new_password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'new_password.symbols' => 'Le mot de passe doit contenir au moins un caractère spécial.',
            'new_password.uncompromised' => 'Ce mot de passe a été compromis dans une fuite de données. Veuillez en choisir un autre.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
        }

        try {
            $user = Auth::user();

            // Mettre à jour le mot de passe
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Déconnecter toutes les autres sessions
            Auth::logoutOtherDevices($request->current_password);

            // Journaliser le changement de mot de passe
            activity()
                ->causedBy($user)
                ->log('Mot de passe modifié');

            return redirect()->route('user-profile.show')
                ->with('success', 'Mot de passe changé avec succès!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les informations d'un utilisateur (pour admin)
     */
   public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'poste_id' => 'nullable|exists:postes,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
        }

        try {

            /** 1️⃣ Préparation du tableau */
            $data = [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
                'poste_id' => $request->poste_id,
                'role_id' => $request->role_id,
            ];

            /** 2️⃣ username */
            $data['username'] = $request->filled('username')
                ? $request->username
                : $user->username;

            /** 3️⃣ email */
            $data['email'] = $request->filled('email')
                ? $request->email
                : $user->email;

            /** 4️⃣ is_active */
            $data['is_active'] = $request->has('is_active')
                ? $request->is_active
                : $user->is_active;

            /** 5️⃣ Photo */
            if ($request->hasFile('photo')) {

                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }

                $data['photo'] = $request->file('photo')->store('photos/users', 'public');
            }

            /** 6️⃣ Mise à jour */
            $user->update($data);

            /** 7️⃣ Rôle Spatie */
            $role = Role::find($request->role_id);
            if ($role) {
                $user->syncRoles([$role->name]);
            }

            Alert::success('Mis à jour', 'Utilisateur mis à jour avec succès!');
            return redirect()->route('user-profile.show', $user->id);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }



    /**
     * Désactiver un utilisateur
     */
    public function deactivate($id)
    {
        if (!Auth::user()->hasRole('admin | super-admin')) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::findOrFail($id);

        // Empêcher la désactivation de soi-même
        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'Utilisateur désactivé avec succès.');
    }

    /**
     * Activer un utilisateur
     */
    public function activate($id)
    {
        if (!Auth::user()->hasRole('admin | super-admin')) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Utilisateur activé avec succès.');
    }

    /**
     * Télécharger la photo de profil
     */
    public function downloadPhoto($id)
    {
        $user = User::findOrFail($id);

        if (!$user->photo) {
            abort(404, 'Photo non trouvée.');
        }

        $path = storage_path('app/public/' . $user->photo);

        if (!file_exists($path)) {
            abort(404, 'Fichier non trouvé.');
        }

        return response()->download($path);
    }

}
