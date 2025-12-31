<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Poste;
use App\Models\DailyEntry;
use App\Models\TimeEntry;
use App\Models\Conge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class UserProfileController extends Controller
{
    /**
     * Afficher et éditer le profil de l'utilisateur connecté
     */
    public function editUser($id)
    {
        $user = Auth::user();

        // Charger les relations nécessaires pour la gestion du temps
        $user->load([
            'poste',
            'creator',
            'dailyEntries' => function($query) {
                $query->latest('jour')->limit(10);
            },
            'timeEntries' => function($query) {
                $query->latest()->limit(10);
            },
            'conges' => function($query) {
                $query->latest()->limit(5);
            }
        ]);

        // Récupérer la liste des postes pour le formulaire
        $postes = Poste::orderBy('intitule')->get();

        // Récupérer les rôles Spatie pour le formulaire (admin uniquement)
        $roles = Role::orderBy('name')->get();

        // Calculer les statistiques de temps
        $statistiques = $this->calculerStatistiquesTemps($user);

        return view('profile.edit', compact('user', 'postes', 'roles', 'statistiques'));
    }

    /**
     * Afficher le profil d'un autre utilisateur (pour admin)
     */
    public function showUser($id)
    {
        // Vérifier les permissions
        if (!Auth::user()->hasRole('admin|super-admin') && Auth::id() != $id) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::with([
            'poste',
            'creator',
            'roles', // Charger les rôles Spatie
            'dailyEntries' => function($query) {
                $query->latest('jour')->limit(10);
            },
            'timeEntries' => function($query) {
                $query->with('dossier')->latest()->limit(10);
            },
            'conges' => function($query) {
                $query->latest()->limit(5);
            }
        ])->findOrFail($id);

        // Récupérer la liste des postes
        $postes = Poste::orderBy('intitule')->get();

        // Calculer les statistiques
        $statistiques = $this->calculerStatistiquesTemps($user);

        return view('profile.show', compact('user', 'postes', 'statistiques'));
    }

    /**
     * Calculer les statistiques de temps pour un utilisateur
     */
    private function calculerStatistiquesTemps($user)
    {
        $now = Carbon::now();
        $debutMois = $now->copy()->startOfMonth();
        $finMois = $now->copy()->endOfMonth();

        // Statistiques globales
        $totalDailyEntries = $user->dailyEntries()->count();
        $totalTimeEntries = $user->timeEntries()->count();
        $totalConges = $user->conges()->count();

        // Heures du mois en cours
        $heuresMoisEnCours = $user->dailyEntries()
            ->whereBetween('jour', [$debutMois, $finMois])
            ->sum('heures_reelles');

        // Heures théoriques du mois
        $heuresTheoriquesMois = $user->dailyEntries()
            ->whereBetween('jour', [$debutMois, $finMois])
            ->sum('heures_theoriques');

        // Écart heures (réelles - théoriques)
        $ecartHeures = $heuresMoisEnCours - $heuresTheoriquesMois;

        // Taux de réalisation
        $tauxRealisation = $heuresTheoriquesMois > 0
            ? round(($heuresMoisEnCours / $heuresTheoriquesMois) * 100, 1)
            : 0;

        // Jours de congés pris cette année (calculé depuis les dates)
        $debutAnnee = $now->copy()->startOfYear();
        $congesApprouves = $user->conges()
            ->whereBetween('date_debut', [$debutAnnee, $now])
            ->get();

        // Calculer le total des jours de congé
        $congesPris = $congesApprouves->sum(function($conge) {
            if ($conge->date_debut && $conge->date_fin) {
                return $conge->date_debut->diffInDays($conge->date_fin) + 1;
            }
            return 0;
        });

        // Congés en attente
        $congesEnAttente = $user->conges()
            ->count();

        // Dernière entrée de temps
        $derniereEntree = $user->dailyEntries()
            ->latest('jour')
            ->first();

        // Journées validées ce mois
        $journeesValidees = $user->dailyEntries()
            ->whereBetween('jour', [$debutMois, $finMois])
            ->count();

        // Journées en attente
        $journeesEnAttente = $user->dailyEntries()
            ->count();

        return [
            'total_daily_entries' => $totalDailyEntries,
            'total_time_entries' => $totalTimeEntries,
            'total_conges' => $totalConges,
            'heures_mois_en_cours' => round($heuresMoisEnCours, 2),
            'heures_theoriques_mois' => round($heuresTheoriquesMois, 2),
            'ecart_heures' => round($ecartHeures, 2),
            'taux_realisation' => $tauxRealisation,
            'conges_pris' => $congesPris,
            'conges_en_attente' => $congesEnAttente,
            'derniere_entree' => $derniereEntree,
            'journees_validees' => $journeesValidees,
            'journees_en_attente' => $journeesEnAttente,
        ];
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => [
                'required',
                'current_password'
            ],
            'new_password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
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

            Alert::success('Succès', 'Mot de passe changé avec succès!');
            return redirect()->route('user-profile.show');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les informations d'un utilisateur
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
            'username' => 'nullable|string|max:255|unique:users,username,' . $id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'role_name' => 'nullable|string|exists:roles,name', // Validation du nom du rôle Spatie
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
        }

        try {
            // Préparation du tableau de données
            $data = [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
                'poste_id' => $request->poste_id,
            ];

            // Username
            if ($request->filled('username')) {
                $data['username'] = $request->username;
            }

            // Email
            if ($request->filled('email')) {
                $data['email'] = $request->email;
            }

            // Statut actif (uniquement pour admin)
            if (Auth::user()->hasRole('admin|super-admin')) {
                $data['is_active'] = $request->has('is_active')
                    ? $request->is_active
                    : $user->is_active;
            }

            // Photo
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }

                $data['photo'] = $request->file('photo')->store('photos/users', 'public');
            }

            // Mise à jour
            $user->update($data);

            // Synchroniser le rôle Spatie (uniquement pour admin)
            if (Auth::user()->hasRole('admin|super-admin') && $request->filled('role_name')) {
                $user->syncRoles([$request->role_name]);
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
        if (!Auth::user()->hasRole('admin|super-admin')) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::findOrFail($id);

        // Empêcher la désactivation de soi-même
        if ($user->id === Auth::id()) {
            Alert::warning('Attention', 'Vous ne pouvez pas désactiver votre propre compte.');
            return redirect()->back();
        }

        $user->update(['is_active' => false]);

        Alert::success('Succès', 'Utilisateur désactivé avec succès.');
        return redirect()->back();
    }

    /**
     * Activer un utilisateur
     */
    public function activate($id)
    {
        if (!Auth::user()->hasRole('admin|super-admin')) {
            abort(403, 'Accès non autorisé');
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => true]);

        Alert::success('Succès', 'Utilisateur activé avec succès.');
        return redirect()->back();
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

    /**
     * Exporter le récapitulatif de temps de l'utilisateur (PDF/Excel)
     * À implémenter selon vos besoins
     */
    public function exportTemps($id, $format = 'pdf')
    {
        $user = User::findOrFail($id);

        // Vérifier les permissions
        if (!Auth::user()->hasRole('admin|super-admin') && Auth::id() != $id) {
            abort(403, 'Accès non autorisé');
        }

        // TODO: Implémenter l'export selon le format demandé
        // Exemple: return PDF::loadView('exports.temps', compact('user'))->download();

        Alert::info('Info', 'Fonctionnalité d\'export en cours de développement.');
        return redirect()->back();
    }
}
