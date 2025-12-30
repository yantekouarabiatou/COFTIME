<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TimeEntry;
use App\Models\Dossier;
use App\Models\DailyEntry;
use App\Models\Conge;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Récupérer les données pour le dashboard
     */
    public function data()
    {

        $user = auth()->user();
        // Totaux généraux
        $totals = [
            'clients' => Client::count(),
            'dossiers_actifs' => Dossier::whereIn('statut', ['ouvert', 'en_cours'])->count(),
            'heures_mois' => TimeEntry::whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->where('user_id', $user->id)
                                      ->where('user_id', $user->id)
                                      ->sum('heures'),
            'conges_en_cours' => Conge::where('date_debut', '<=', now())
                                      ->where('date_fin', '>=', now())
                                      ->where('user_id', $user->id)
                                      ->count(),
            'utilisateurs_actifs' => User::whereHas('timeEntries', function($q) {
                                         $q->whereMonth('created_at', now()->month);
                                     })->count(),
        ];

        // Statistiques hebdomadaires (7 derniers jours)
        $weekStart = now()->subDays(7);
        $weeklyStats = [
            'heures' => TimeEntry::where('created_at', '>=', $weekStart)->sum('heures'),
            'dossiers' => Dossier::where('created_at', '>=', $weekStart)->count(),
            'clients' => Client::where('created_at', '>=', $weekStart)->count(),
            'conges' => Conge::where('created_at', '>=', $weekStart)->count(),
        ];

        // Statistiques du mois précédent (pour comparaison)
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $lastMonthStats = [
            'heures' => TimeEntry::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('heures'),
            'dossiers' => Dossier::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
            'clients' => Client::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
            'conges' => Conge::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
        ];

        // Statistiques mensuelles (mois en cours)
        $monthlyStats = [
            'heures' => TimeEntry::whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->sum('heures'),
            'dossiers' => Dossier::whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->count(),
            'clients' => Client::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)
                               ->count(),
            'conges' => Conge::whereMonth('created_at', now()->month)
                             ->whereYear('created_at', now()->year)
                             ->count(),
        ];

        // Calculer les pourcentages d'évolution
        $percentages = [
            'heures' => $this->calculatePercentage($monthlyStats['heures'], $lastMonthStats['heures']),
            'dossiers' => $this->calculatePercentage($monthlyStats['dossiers'], $lastMonthStats['dossiers']),
            'clients' => $this->calculatePercentage($monthlyStats['clients'], $lastMonthStats['clients']),
            'conges' => $this->calculatePercentage($monthlyStats['conges'], $lastMonthStats['conges']),
        ];

        // Données des 30 derniers jours
        $last30days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateLabel = $date->format('d/m');
            
            $last30days->put($dateLabel, [
                'heures' => TimeEntry::whereDate('created_at', $date)->sum('heures') ?? 0,
                'dossiers' => Dossier::whereDate('created_at', $date)->count(),
                'clients' => Client::whereDate('created_at', $date)->count(),
            ]);
        }

        // Top 5 utilisateurs par heures travaillées (mois en cours)
        $topUsers = User::select('users.id', 'users.prenom', 'users.nom')
            ->leftJoin('time_entries', 'users.id', '=', 'time_entries.user_id')
            ->whereMonth('time_entries.created_at', now()->month)
            ->whereYear('time_entries.created_at', now()->year)
            ->groupBy('users.id', 'users.prenom', 'users.nom')
            ->selectRaw('SUM(time_entries.heures) as total_heures')
            ->orderByDesc('total_heures')
            ->limit(5)
            ->get();

        // Dossiers les plus actifs (par heures)
        $topDossiers = Dossier::select('dossiers.id', 'dossiers.nom', 'dossiers.reference')
            ->leftJoin('time_entries', 'dossiers.id', '=', 'time_entries.dossier_id')
            ->whereMonth('time_entries.created_at', now()->month)
            ->whereYear('time_entries.created_at', now()->year)
            ->groupBy('dossiers.id', 'dossiers.nom', 'dossiers.reference')
            ->selectRaw('SUM(time_entries.heures) as total_heures')
            ->orderByDesc('total_heures')
            ->limit(5)
            ->get();

        // Répartition des congés par type (mois en cours)
        $congesParType = Conge::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->select('type_conge', DB::raw('count(*) as count'))
            ->groupBy('type_conge')
            ->get();

        // Statistiques par statut de dossier
        $dossiersParStatut = Dossier::select('statut', DB::raw('count(*) as count'))
            ->groupBy('statut')
            ->get();

        // Heures par dossier (top 10)
        $heuresParDossier = Dossier::select('dossiers.nom', 'dossiers.reference')
            ->leftJoin('time_entries', 'dossiers.id', '=', 'time_entries.dossier_id')
            ->whereMonth('time_entries.created_at', now()->month)
            ->whereYear('time_entries.created_at', now()->year)
            ->groupBy('dossiers.id', 'dossiers.nom', 'dossiers.reference')
            ->selectRaw('SUM(time_entries.heures) as total_heures')
            ->orderByDesc('total_heures')
            ->limit(10)
            ->get();

        return response()->json([
            'totals' => $totals,
            'weekly' => $weeklyStats,
            'monthly' => $monthlyStats,
            'percentages' => $percentages,
            'last30days' => [
                'dates' => $last30days->keys()->toArray(),
                'heures' => $last30days->pluck('heures')->toArray(),
                'dossiers' => $last30days->pluck('dossiers')->toArray(),
                'clients' => $last30days->pluck('clients')->toArray(),
            ],
            'topUsers' => [
                'names' => $topUsers->pluck('prenom')->map(fn($n) => ucfirst($n))->toArray(),
                'heures' => $topUsers->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            ],
            'topDossiers' => [
                'names' => $topDossiers->pluck('nom')->toArray(),
                'heures' => $topDossiers->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            ],
            'congesParType' => [
                'types' => $congesParType->pluck('type_conge')->toArray(),
                'counts' => $congesParType->pluck('count')->toArray(),
            ],
            'dossiersParStatut' => [
                'statuts' => $dossiersParStatut->pluck('statut')->toArray(),
                'counts' => $dossiersParStatut->pluck('count')->toArray(),
            ],
            'heuresParDossier' => [
                'dossiers' => $heuresParDossier->pluck('nom')->toArray(),
                'heures' => $heuresParDossier->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            ],
        ]);
    }

    /**
     * Calculer le pourcentage d'évolution
     */
    private function calculatePercentage($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? '+100' : '0';
        }
        
        $percentage = (($current - $previous) / $previous) * 100;
        $sign = $percentage >= 0 ? '+' : '';
        
        return $sign . number_format($percentage, 0);
    }

    /**
     * Statistiques par utilisateur
     */
    public function userStats($userId)
    {
        $user = User::findOrFail($userId);
        
        // Heures ce mois
        $heuresMois = TimeEntry::where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('heures');

        // Heures totales
        $heuresTotales = TimeEntry::where('user_id', $userId)->sum('heures');

        // Congés ce mois
        $congesMois = Conge::where('user_id', $userId)
            ->where(function($q) {
                $q->whereMonth('date_debut', now()->month)
                  ->orWhereMonth('date_fin', now()->month);
            })
            ->whereYear('date_debut', now()->year)
            ->count();

        // Dossiers actifs
        $dossiersActifs = Dossier::whereHas('timeEntries', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->whereIn('statut', ['ouvert', 'en_cours'])
            ->count();

        // Heures par jour (7 derniers jours)
        $heuresJournalieres = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $heures = TimeEntry::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->sum('heures') ?? 0;
            
            $heuresJournalieres->put($date->format('d/m'), $heures);
        }

        return response()->json([
            'user' => [
                'name' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
            ],
            'stats' => [
                'heures_mois' => round($heuresMois, 2),
                'heures_totales' => round($heuresTotales, 2),
                'conges_mois' => $congesMois,
                'dossiers_actifs' => $dossiersActifs,
            ],
            'heuresJournalieres' => [
                'dates' => $heuresJournalieres->keys()->toArray(),
                'heures' => $heuresJournalieres->values()->toArray(),
            ]
        ]);
    }

    /**
     * Statistiques par dossier
     */
    public function dossierStats($dossierId)
    {
        $dossier = Dossier::with('client')->findOrFail($dossierId);
        
        // Heures totales
        $heuresTotales = TimeEntry::where('dossier_id', $dossierId)->sum('heures');

        // Heures ce mois
        $heuresMois = TimeEntry::where('dossier_id', $dossierId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('heures');

        // Nombre d'intervenants
        $intervenants = TimeEntry::where('dossier_id', $dossierId)
            ->distinct('user_id')
            ->count('user_id');

        // Répartition par utilisateur
        $repartitionUsers = User::select('users.prenom', 'users.nom')
            ->join('time_entries', 'users.id', '=', 'time_entries.user_id')
            ->where('time_entries.dossier_id', $dossierId)
            ->groupBy('users.id', 'users.prenom', 'users.nom')
            ->selectRaw('SUM(time_entries.heures) as total_heures')
            ->orderByDesc('total_heures')
            ->get();

        return response()->json([
            'dossier' => [
                'nom' => $dossier->nom,
                'reference' => $dossier->reference,
                'client' => $dossier->client->nom ?? 'N/A',
                'statut' => $dossier->statut,
            ],
            'stats' => [
                'heures_totales' => round($heuresTotales, 2),
                'heures_mois' => round($heuresMois, 2),
                'intervenants' => $intervenants,
                'budget' => $dossier->budget,
            ],
            'repartitionUsers' => [
                'users' => $repartitionUsers->map(fn($u) => $u->prenom . ' ' . $u->nom)->toArray(),
                'heures' => $repartitionUsers->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            ]
        ]);
    }

    /**
     * Exporter les statistiques
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'excel'); // excel ou pdf
        $periode = $request->get('periode', 'mois'); // jour, semaine, mois, annee
        
        // Logique d'exportation à implémenter
        // Utiliser Laravel Excel ou DomPDF
        
        return response()->json([
            'message' => 'Export en cours de développement',
            'type' => $type,
            'periode' => $periode
        ]);
    }

    /**
     * Statistiques des congés
     */
    public function congesStats()
    {
        // Congés en cours
        $congesEnCours = Conge::where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->with('user:id,prenom,nom')
            ->get();

        // Congés à venir (prochains 30 jours)
        $congesAVenir = Conge::where('date_debut', '>', now())
            ->where('date_debut', '<=', now()->addDays(30))
            ->with('user:id,prenom,nom')
            ->orderBy('date_debut')
            ->get();

        // Congés par type (année en cours)
        $congesParType = Conge::whereYear('date_debut', now()->year)
            ->select('type_conge', DB::raw('count(*) as count'))
            ->groupBy('type_conge')
            ->get();

        return response()->json([
            'congesEnCours' => $congesEnCours,
            'congesAVenir' => $congesAVenir,
            'congesParType' => [
                'types' => $congesParType->pluck('type_conge')->toArray(),
                'counts' => $congesParType->pluck('count')->toArray(),
            ]
        ]);
    }
}