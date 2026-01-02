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

class StatisticsController extends Controller
{
    /**
     * Afficher la page des statistiques globales (Admin uniquement)
     */
    public function index()
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Accès non autorisé');
        }

        return view('pages.statistics.statglobale');
    }

    /**
     * Récupérer les statistiques globales avec filtres
     */
    public function globalStats(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        // Récupérer les filtres
        $periode = $request->get('periode', 'mois'); // jour, semaine, mois, annee, personnalise
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $userId = $request->get('user_id'); // Filtre par employé spécifique

        // Définir les dates selon la période
        [$startDate, $endDate] = $this->getDateRange($periode, $dateDebut, $dateFin);

        // Statistiques globales
        $stats = [
            'totaux' => $this->getTotauxGlobaux($startDate, $endDate, $userId),
            'classement_employes' => $this->getClassementEmployes($startDate, $endDate),
            'classement_conges' => $this->getClassementConges($startDate, $endDate),
            'evolution_heures' => $this->getEvolutionHeures($startDate, $endDate, $userId),
            'repartition_dossiers' => $this->getRepartitionDossiers($startDate, $endDate, $userId),
            'statistiques_conges' => $this->getStatistiquesConges($startDate, $endDate, $userId),
            'performance_mensuelle' => $this->getPerformanceMensuelle($startDate, $endDate, $userId),
            'taux_validation' => $this->getTauxValidation($startDate, $endDate, $userId),
            'heures_par_jour_semaine' => $this->getHeuresParJourSemaine($startDate, $endDate, $userId),
        ];

        return response()->json([
            'stats' => $stats,
            'periode' => [
                'type' => $periode,
                'debut' => $startDate->format('Y-m-d'),
                'fin' => $endDate->format('Y-m-d'),
            ],
            'filtre_user' => $userId ? User::find($userId)->prenom . ' ' . User::find($userId)->nom : null,
        ]);
    }

    /**
     * Obtenir la plage de dates selon la période
     */
    private function getDateRange($periode, $dateDebut = null, $dateFin = null)
    {
        switch ($periode) {
            case 'jour':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'semaine':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'mois':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'annee':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'personnalise':
                $startDate = $dateDebut ? Carbon::parse($dateDebut)->startOfDay() : now()->startOfMonth();
                $endDate = $dateFin ? Carbon::parse($dateFin)->endOfDay() : now()->endOfMonth();
                break;
            default:
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
        }

        return [$startDate, $endDate];
    }

    /**
     * Totaux globaux
     */
    private function getTotauxGlobaux($startDate, $endDate, $userId = null)
    {
        $timeEntriesQuery = TimeEntry::whereBetween('created_at', [$startDate, $endDate]);
        $congesQuery = Conge::where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('date_debut', [$startDate, $endDate])
              ->orWhereBetween('date_fin', [$startDate, $endDate])
              ->orWhere(function($q2) use ($startDate, $endDate) {
                  $q2->where('date_debut', '<=', $startDate)
                     ->where('date_fin', '>=', $endDate);
              });
        });

        if ($userId) {
            $timeEntriesQuery->where('user_id', $userId);
            $congesQuery->where('user_id', $userId);
        }

        return [
            'total_employes' => $userId ? 1 : User::count(),
            'employes_actifs' => $userId ? 1 : User::whereHas('timeEntries', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })->count(),
            'total_heures' => round($timeEntriesQuery->sum('heures_reelles'), 2),
            'total_dossiers' => $userId 
                ? Dossier::whereHas('timeEntries', function($q) use ($userId, $startDate, $endDate) {
                    $q->where('user_id', $userId)
                      ->whereBetween('created_at', [$startDate, $endDate]);
                })->count()
                : Dossier::whereBetween('created_at', [$startDate, $endDate])->count(),
            'dossiers_actifs' => Dossier::whereIn('statut', ['ouvert', 'en_cours'])->count(),
            'total_conges' => $congesQuery->count(),
            'conges_en_cours' => Conge::where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->when($userId, function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->count(),
            'total_clients' => Client::count(),
            'moyenne_heures_employe' => $userId ? null : round(
                $timeEntriesQuery->sum('heures_reelles') / max(User::count(), 1),
                2
            ),
        ];
    }

    /**
     * Classement des employés par heures travaillées
     */
    private function getClassementEmployes($startDate, $endDate)
    {
        return User::select('users.id', 'users.prenom', 'users.nom', 'users.email')
            ->leftJoin('time_entries', 'users.id', '=', 'time_entries.user_id')
            ->whereBetween('time_entries.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.prenom', 'users.nom', 'users.email')
            ->selectRaw('SUM(time_entries.heures_reelles) as total_heures')
            ->selectRaw('COUNT(DISTINCT time_entries.dossier_id) as nombre_dossiers')
            ->orderByDesc('total_heures')
            ->limit(20)
            ->get()
            ->map(function($user, $index) {
                return [
                    'rang' => $index + 1,
                    'id' => $user->id,
                    'nom_complet' => $user->prenom . ' ' . $user->nom,
                    'email' => $user->email,
                    'total_heures' => round($user->total_heures ?? 0, 2),
                    'nombre_dossiers' => $user->nombre_dossiers ?? 0,
                ];
            });
    }

    /**
     * Classement des employés par nombre de congés
     */
    private function getClassementConges($startDate, $endDate)
    {
        return User::select('users.id', 'users.prenom', 'users.nom', 'users.email')
            ->leftJoin('conges', 'users.id', '=', 'conges.user_id')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('conges.date_debut', [$startDate, $endDate])
                  ->orWhereBetween('conges.date_fin', [$startDate, $endDate]);
            })
            ->groupBy('users.id', 'users.prenom', 'users.nom', 'users.email')
            ->selectRaw('COUNT(conges.id) as nombre_conges')
            ->selectRaw('SUM(DATEDIFF(conges.date_fin, conges.date_debut) + 1) as total_jours_conges')
            ->orderByDesc('nombre_conges')
            ->limit(20)
            ->get()
            ->map(function($user, $index) {
                return [
                    'rang' => $index + 1,
                    'id' => $user->id,
                    'nom_complet' => $user->prenom . ' ' . $user->nom,
                    'email' => $user->email,
                    'nombre_conges' => $user->nombre_conges ?? 0,
                    'total_jours' => $user->total_jours_conges ?? 0,
                ];
            });
    }

    /**
     * Évolution des heures dans la période
     */
    private function getEvolutionHeures($startDate, $endDate, $userId = null)
    {
        $diffInDays = $startDate->diffInDays($endDate);
        $groupBy = 'DATE(time_entries.created_at)';
        $format = '%Y-%m-%d';
        
        // Adapter le groupement selon la durée
        if ($diffInDays > 90) {
            $groupBy = 'DATE_FORMAT(time_entries.created_at, "%Y-%m")';
            $format = '%Y-%m';
        } elseif ($diffInDays > 365) {
            $groupBy = 'YEAR(time_entries.created_at)';
            $format = '%Y';
        }

        $query = TimeEntry::selectRaw("$groupBy as periode")
            ->selectRaw('SUM(heures_reelles) as total_heures')
            ->selectRaw('COUNT(DISTINCT user_id) as nombre_employes')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $data = $query->groupBy('periode')
            ->orderBy('periode')
            ->get();

        return [
            'labels' => $data->pluck('periode')->map(function($date) use ($diffInDays) {
                if ($diffInDays > 90) {
                    return Carbon::parse($date . '-01')->format('M Y');
                }
                return Carbon::parse($date)->format('d/m');
            })->toArray(),
            'heures' => $data->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            'employes' => $data->pluck('nombre_employes')->toArray(),
        ];
    }

    /**
     * Répartition des heures par dossier
     */
    private function getRepartitionDossiers($startDate, $endDate, $userId = null)
    {
        $query = Dossier::select('dossiers.nom', 'dossiers.reference', 'dossiers.type_dossier')
            ->join('time_entries', 'dossiers.id', '=', 'time_entries.dossier_id')
            ->whereBetween('time_entries.created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('time_entries.user_id', $userId);
        }

        $data = $query->groupBy('dossiers.id', 'dossiers.nom', 'dossiers.reference', 'dossiers.type_dossier')
            ->selectRaw('SUM(time_entries.heures_reelles) as total_heures')
            ->selectRaw('COUNT(DISTINCT time_entries.user_id) as nombre_intervenants')
            ->orderByDesc('total_heures')
            ->limit(10)
            ->get();

        return [
            'dossiers' => $data->pluck('nom')->toArray(),
            'heures' => $data->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
            'intervenants' => $data->pluck('nombre_intervenants')->toArray(),
            'types' => $data->pluck('type_dossier')->toArray(),
        ];
    }

    /**
     * Statistiques des congés
     */
    private function getStatistiquesConges($startDate, $endDate, $userId = null)
    {
        $query = Conge::whereBetween('date_debut', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $congesParType = $query->select('type_conge', DB::raw('count(*) as count'))
            ->groupBy('type_conge')
            ->get();

        return [
            'types' => $congesParType->pluck('type_conge')->toArray(),
            'counts' => $congesParType->pluck('count')->toArray(),
        ];
    }

    /**
     * Performance mensuelle (comparaison mois par mois)
     */
    private function getPerformanceMensuelle($startDate, $endDate, $userId = null)
    {
        $query = TimeEntry::selectRaw('MONTH(created_at) as mois')
            ->selectRaw('YEAR(created_at) as annee')
            ->selectRaw('SUM(heures_reelles) as total_heures')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $data = $query->groupBy('annee', 'mois')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get();

        return [
            'labels' => $data->map(function($item) {
                return Carbon::create($item->annee, $item->mois)->format('M Y');
            })->toArray(),
            'heures' => $data->pluck('total_heures')->map(fn($h) => round($h, 2))->toArray(),
        ];
    }

    /**
     * Taux de validation des saisies
     */
    private function getTauxValidation($startDate, $endDate, $userId = null)
    {
        $query = DailyEntry::whereBetween('jour', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $stats = $query->select('statut', DB::raw('count(*) as count'))
            ->groupBy('statut')
            ->get();

        $total = $stats->sum('count');

        return [
            'statuts' => $stats->pluck('statut')->toArray(),
            'counts' => $stats->pluck('count')->toArray(),
            'pourcentages' => $stats->map(function($item) use ($total) {
                return $total > 0 ? round(($item->count / $total) * 100, 1) : 0;
            })->toArray(),
        ];
    }

    /**
     * Heures par jour de la semaine
     */
    private function getHeuresParJourSemaine($startDate, $endDate, $userId = null)
    {
        $query = TimeEntry::selectRaw('DAYOFWEEK(created_at) as jour')
            ->selectRaw('SUM(heures_reelles) as total_heures')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $data = $query->groupBy('jour')
            ->orderBy('jour')
            ->get();

        $jours = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
        
        // Réorganiser pour commencer par Lundi
        $heuresParJour = array_fill(0, 7, 0);
        foreach ($data as $item) {
            $heuresParJour[$item->jour - 1] = round($item->total_heures, 2);
        }

        // Décaler pour commencer par lundi
        $lundi = array_slice($heuresParJour, 1, 6);
        $dimanche = array_slice($heuresParJour, 0, 1);
        $heuresParJour = array_merge($lundi, $dimanche);
        
        $joursOrdre = array_merge(array_slice($jours, 1, 6), array_slice($jours, 0, 1));

        return [
            'jours' => $joursOrdre,
            'heures' => $heuresParJour,
        ];
    }

    /**
     * Liste des employés pour le filtre
     */
    public function getEmployes()
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $employes = User::select('id', 'prenom', 'nom', 'email')
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'nom_complet' => $user->prenom . ' ' . $user->nom,
                    'email' => $user->email,
                ];
            });

        return response()->json($employes);
    }

    /**
     * Statistiques détaillées d'un employé
     */
    public function employeDetails($userId)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $user = User::findOrFail($userId);

        $stats = [
            'user' => [
                'id' => $user->id,
                'nom_complet' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
            ],
            'heures_totales' => round(TimeEntry::where('user_id', $userId)->sum('heures_reelles'), 2),
            'heures_mois' => round(TimeEntry::where('user_id', $userId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('heures_reelles'), 2),
            'heures_annee' => round(TimeEntry::where('user_id', $userId)
                ->whereYear('created_at', now()->year)
                ->sum('heures_reelles'), 2),
            'nombre_dossiers' => Dossier::whereHas('timeEntries', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'nombre_conges_annee' => Conge::where('user_id', $userId)
                ->whereYear('date_debut', now()->year)
                ->count(),
            'jours_conges_annee' => Conge::where('user_id', $userId)
                ->whereYear('date_debut', now()->year)
                ->get()
                ->sum(function($conge) {
                    return Carbon::parse($conge->date_debut)->diffInDays(Carbon::parse($conge->date_fin)) + 1;
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Exporter les statistiques
     */
    public function export(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $type = $request->get('type', 'excel'); // excel, pdf, csv
        $periode = $request->get('periode', 'mois');
        
        // TODO: Implémenter l'export avec Laravel Excel ou DomPDF
        
        return response()->json([
            'message' => 'Export en cours de développement',
            'type' => $type,
            'periode' => $periode,
        ]);
    }
}
