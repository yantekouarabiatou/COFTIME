<?php

namespace App\Http\Controllers;

use App\Models\LogActivite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate; // Important pour les checks de permission

class LogActivitesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Optionnel: Middleware global pour l'accès aux logs si vous avez une permission spécifique
        // $this->middleware('permission:accéder au tableau de bord admin')->only(['index']);
    }

    /**
     * Display a listing of the activity logs.
     */
    public function index()
    {
        $user = auth()->user();

        // On initialise la requête de base
        $query = LogActivite::with('user')->latest();

        // Si l'utilisateur est Super Admin ou Admin, il voit TOUT
        // On utilise la permission "accéder au tableau de bord admin" comme proxy pour "voir tous les logs"
        // OU on vérifie directement les rôles via Spatie
        if ($user->hasRole(['super-admin', 'admin'])) {
            // Aucune restriction, ils voient tout
        } else {
            // Sinon, l'utilisateur ne voit que SES propres actions
            $query->where('user_id', $user->id);
        }

        $logs = $query->paginate(50);

        return view('pages.logs.index', compact('logs'));
    }

    public function show(LogActivite $log)
    {
        $user = auth()->user();

        // SÉCURITÉ : Empêcher un utilisateur lambda de voir le log d'un autre via l'URL
        if (!$user->hasRole(['super-admin', 'admin']) && $log->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas la permission de consulter ce journal d\'activité.');
        }

        $log->loadMissing(['user', 'loggable']);

        if ($log->loggable && method_exists($log->loggable, 'withTrashed')) {
            $log->loadMissing(['loggable' => fn($q) => $q->withTrashed()]);
        }

        return view('pages.logs.show', compact('log'));
    }
}
