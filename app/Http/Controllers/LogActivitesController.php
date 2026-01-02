<?php

namespace App\Http\Controllers;

use App\Models\LogActivite;
use Illuminate\Http\Request;

class LogActivitesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Liste des logs d'activité
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Requête de base optimisée : on ne sélectionne QUE les colonnes nécessaires
        $query = LogActivite::query()
            ->select([
                'id',
                'user_id',
                'action',
                'loggable_type',
                'loggable_id',
                'description',
                'ip_address',
                'created_at',
            ])
            ->with('user:id,prenom,nom') // On charge seulement les champs utiles de l'utilisateur
            ->latest('created_at'); // ORDER BY created_at DESC

        // Restriction selon le rôle
        if (!$user->hasRole(['super-admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

        // Pagination optimisée (50 par page)
        $logs = $query->paginate(50);

        // Astuce : on garde l'URL propre pour la pagination
        $logs->appends($request->all());

        return view('pages.logs.index', compact('logs'));
    }

    /**
     * Affichage d'un log détaillé
     */
    public function show(LogActivite $log)
    {
        $user = auth()->user();

        // Sécurité : un utilisateur normal ne voit que ses propres logs
        if (!$user->hasRole(['super-admin', 'admin']) && $log->user_id !== $user->id) {
            abort(403, 'Accès refusé.');
        }

        // Chargement intelligent des relations
        $log->loadMissing([
            'user:id,prenom,nom',
            'loggable' => function ($query) {
                $query->withTrashed(); // Pour voir même les ressources supprimées
            }
        ]);

        return view('pages.logs.show', compact('log'));
    }
}