<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Independance;
use App\Models\User;
use Carbon\Carbon;

class IndependanceReportController extends Controller
{
    public function annual()
    {
        $currentYear = date('Y');
        $startDate = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();

        // Récupérer toutes les déclarations de l'année
        $independances = Independance::whereBetween('created_at', [$startDate, $endDate])->get();

        // Calculer les statistiques
        $stats = $this->calculateStats($independances, $currentYear);

        return view('pages.statistics.independances', [
            'stats' => $stats,
            'currentYear' => $currentYear
        ]);
    }

    private function calculateStats($independances, $year)
    {
        // Pour les questions d'indépendance
        $questions_oui = $independances->filter(function ($item) {
            return !empty($item->question_independance) && trim($item->question_independance) !== '';
        })->count();

        $questions_non = $independances->filter(function ($item) {
            return empty($item->question_independance) || trim($item->question_independance) === '';
        })->count();

        $stats = [
            // Totaux généraux
            'total_declarations' => $independances->count(),
            'total_frais' => $independances->sum('total_frais'),
            'total_honoraires' => $independances->sum('total_honoraires'),

            // Moyennes
            'avg_frais_audit' => $independances->avg('frais_audit') ?? 0,
            'avg_frais_non_audit' => $independances->avg('frais_non_audit') ?? 0,
            'avg_experience' => $independances->avg('nombres_annees_experiences') ?? 0,
            'honoraires_avg' => $independances->avg('total_honoraires') ?? 0,

            // Maxima pour les progress bars
            'max_frais_audit' => $independances->max('frais_audit') ?? 1,
            'max_frais_non_audit' => $independances->max('frais_non_audit') ?? 1,

            // Ratios
            'ratio_frais_non_audit' => $independances->sum('frais_non_audit') > 0
                ? round(($independances->sum('frais_non_audit') / $independances->sum('total_frais')) * 100, 1)
                : 0,

            // Évolution
            'evolution_percentage' => $this->calculateEvolution($year),

            // Distribution par type d'entité
            'entity_types' => $this->getEntityTypes($independances),

            // Données mensuelles
            'monthly_data' => $this->getMonthlyData($independances, $year),

            // Top clients
            'top_clients' => $this->getTopClients($independances),

            // Distribution d'expérience
            'exp_0_5' => $this->getExperienceDistribution($independances, 0, 5),
            'exp_5_10' => $this->getExperienceDistribution($independances, 5, 10),
            'exp_10_plus' => $this->getExperienceDistribution($independances, 10, null),

            // Questions d'indépendance (corrigé)
            'questions_oui' => $questions_oui,
            'questions_non' => $questions_non,

            // Top responsables
            'top_responsables' => $this->getTopResponsables($independances),
            'max_mission_count' => $this->getMaxMissionCount($independances),

            // Clients par type
            'clients_par_type' => $this->getClientsByType($independances),

            // Taux de conformité (calcul simplifié)
            'compliance_rate' => $this->calculateComplianceRate($independances),

            // Déclarations par mois
            'declarations_per_month' => round($independances->count() / 12, 1),

            // Plage de dates
            'date_range' => [
                'start' => $independances->min('created_at')?->format('d/m/Y') ?? '01/01/' . $year,
                'end' => $independances->max('created_at')?->format('d/m/Y') ?? '31/12/' . $year
            ]
        ];

        return $stats;
    }

    private function calculateEvolution($currentYear)
    {
        $previousYear = $currentYear - 1;
        $prevStart = Carbon::createFromDate($previousYear, 1, 1)->startOfDay();
        $prevEnd = Carbon::createFromDate($previousYear, 12, 31)->endOfDay();

        $currentCount = Independance::whereYear('created_at', $currentYear)->count();
        $previousCount = Independance::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        if ($previousCount > 0) {
            return round((($currentCount - $previousCount) / $previousCount) * 100, 1);
        }

        return 0;
    }

    private function getEntityTypes($independances)
    {
        $types = $independances->groupBy('type_entite')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->take(5);

        return [
            'labels' => $types->keys()->toArray(),
            'data' => $types->values()->toArray()
        ];
    }

    private function getMonthlyData($independances, $year)
    {
        $monthlyData = collect(range(1, 12))->mapWithKeys(function ($month) use ($independances, $year) {
            $monthCount = $independances->filter(
                fn($item) =>
                $item->created_at->month == $month && $item->created_at->year == $year
            )->count();

            $monthFrais = $independances->filter(
                fn($item) =>
                $item->created_at->month == $month && $item->created_at->year == $year
            )->sum('total_frais');

            $monthHonoraires = $independances->filter(
                fn($item) =>
                $item->created_at->month == $month && $item->created_at->year == $year
            )->sum('total_honoraires');

            return [
                $month => [
                    'count' => $monthCount,
                    'frais' => $monthFrais,
                    'honoraires' => $monthHonoraires
                ]
            ];
        });

        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        return [
            'labels' => $months,
            'counts' => $monthlyData->pluck('count')->toArray(),
            'frais' => $monthlyData->pluck('frais')->toArray(),
            'honoraires' => $monthlyData->pluck('honoraires')->toArray()
        ];
    }

    private function getTopClients($independances, $limit = 5)
    {
        return $independances->groupBy('nom_client')
            ->map(function ($group, $clientName) {
                $first = $group->first();
                return [
                    'nom_client' => $clientName,
                    'total_frais' => $group->sum('total_frais'),
                    'total_honoraires' => $group->sum('total_honoraires'),
                    'type_entite' => $first->type_entite ?? 'Non spécifié',
                    'siege_social' => $first->siege_social ?? null
                ];
            })
            ->sortByDesc('total_frais')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getExperienceDistribution($independances, $min, $max)
    {
        $filtered = $independances->filter(function ($item) use ($min, $max) {
            $exp = $item->nombres_annees_experiences ?? 0;

            if ($max === null) {
                return $exp >= $min;
            }

            return $exp >= $min && $exp < $max;
        });

        $total = $independances->count();
        return $total > 0 ? round(($filtered->count() / $total) * 100, 1) : 0;
    }

    private function getTopResponsables($independances, $limit = 6)
    {
        // Cette méthode nécessite que vous ayez une relation avec les utilisateurs
        // Pour l'exemple, on utilise les données brutes

        $responsables = [];

        foreach ($independances as $independance) {
            $userIds = $independance->responsable_audit;

            if (is_array($userIds) && !empty($userIds)) {
                foreach ($userIds as $userId) {
                    if (!isset($responsables[$userId])) {
                        $user = User::find($userId);
                        $responsables[$userId] = [
                            'id' => $userId,
                            'name' => $user ? trim($user->nom . ' ' . $user->prenom) : "User #$userId",
                            'count' => 0,
                            'total_frais' => 0
                        ];
                    }

                    $responsables[$userId]['count']++;
                    $responsables[$userId]['total_frais'] += $independance->total_frais;
                }
            }
        }

        return collect($responsables)
            ->sortByDesc('count')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getMaxMissionCount($independances)
    {
        $responsables = [];

        foreach ($independances as $independance) {
            $userIds = $independance->responsable_audit;

            if (is_array($userIds) && !empty($userIds)) {
                foreach ($userIds as $userId) {
                    if (!isset($responsables[$userId])) {
                        $responsables[$userId] = 0;
                    }

                    $responsables[$userId]++;
                }
            }
        }

        return !empty($responsables) ? max($responsables) : 1;
    }

    private function getClientsByType($independances)
    {
        return $independances->groupBy('type_entite')
            ->map(fn($group) => $group->count())
            ->toArray();
    }

    private function calculateComplianceRate($independances)
    {
        // Logique de calcul du taux de conformité
        // Pour l'exemple, on considère que c'est le pourcentage de déclarations complètes
        $completeDeclarations = $independances->filter(function ($item) {
            return !empty($item->nom_client) &&
                !empty($item->type_entite) &&
                $item->frais_audit !== null;
        })->count();

        $total = $independances->count();

        return $total > 0 ? round(($completeDeclarations / $total) * 100, 1) : 0;
    }
}
