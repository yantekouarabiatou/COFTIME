<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CadeauInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CadeauInvitationReportController extends Controller
{
    public function annual()
    {
        $currentYear = date('Y');
        $startDate = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();

        // Récupérer toutes les déclarations de l'année
        $cadeaux = CadeauInvitation::whereBetween('date', [$startDate, $endDate])->get();

        // Calculer les statistiques
        $stats = $this->calculateStats($cadeaux, $currentYear);

        return view('pages.statistics.cadeaux', [
            'stats' => $stats,
            'currentYear' => $currentYear
        ]);
    }

    private function calculateStats($cadeaux, $year)
    {
        // Compter par action
        $acceptes = $cadeaux->where('action_prise', 'accepté')->count();
        $refuses = $cadeaux->where('action_prise', 'refusé')->count();
        $en_attente = $cadeaux->where('action_prise', 'en_attente')->count();
        $non_defini = $cadeaux->filter(function ($cadeau) {
            return empty($cadeau->action_prise) || !in_array($cadeau->action_prise, ['accepté', 'refusé', 'en_attente']);
        })->count();

        // Calculer les valeurs par action
        $valeur_acceptes = $cadeaux->where('action_prise', 'accepté')->sum('valeurs') ?? 0;
        $valeur_refuses = $cadeaux->where('action_prise', 'refusé')->sum('valeurs') ?? 0;
        $valeur_en_attente = $cadeaux->where('action_prise', 'en_attente')->sum('valeurs') ?? 0;
        $valeur_non_defini = $cadeaux->filter(function ($cadeau) {
            return empty($cadeau->action_prise) || !in_array($cadeau->action_prise, ['accepté', 'refusé', 'en_attente']);
        })->sum('valeurs') ?? 0;

        $total_declarations = $cadeaux->count();

        $stats = [
            // Totaux généraux
            'total_declarations' => $total_declarations,
            'total_valeurs' => $cadeaux->sum('valeurs') ?? 0,

            // Par action - quantités
            'acceptes' => $acceptes,
            'refuses' => $refuses,
            'en_attente' => $en_attente,
            'non_defini' => $non_defini,

            // Par action - valeurs
            'valeur_acceptes' => $valeur_acceptes,
            'valeur_refuses' => $valeur_refuses,
            'valeur_en_attente' => $valeur_en_attente,
            'valeur_non_defini' => $valeur_non_defini,

            // Taux par action
            'taux_acceptation' => $total_declarations > 0 ? round(($acceptes / $total_declarations) * 100, 1) : 0,
            'taux_refus' => $total_declarations > 0 ? round(($refuses / $total_declarations) * 100, 1) : 0,
            'taux_en_attente' => $total_declarations > 0 ? round(($en_attente / $total_declarations) * 100, 1) : 0,
            'taux_non_defini' => $total_declarations > 0 ? round(($non_defini / $total_declarations) * 100, 1) : 0,

            // Moyennes et extrêmes
            'valeur_moyenne' => $total_declarations > 0 ? round($cadeaux->avg('valeurs') ?? 0, 0) : 0,
            'max_valeur' => $cadeaux->max('valeurs') ?? 0,
            'mediane_valeurs' => $this->calculateMedian($cadeaux->pluck('valeurs')->filter()->toArray()),

            // Évolution
            'evolution_percentage' => $this->calculateEvolution($year),

            // Distribution par action pour graphique
            'action_distribution' => [
                'labels' => ['Acceptés', 'Refusés', 'En attente', 'Non défini'],
                'data' => [$acceptes, $refuses, $en_attente, $non_defini]
            ],

            // Données mensuelles
            'monthly_data' => $this->getMonthlyData($cadeaux, $year),

            // Top cadeaux par valeur
            'top_cadeaux' => $this->getTopCadeaux($cadeaux),

            // Top responsables
            'top_responsables' => $this->getTopResponsables($cadeaux),
            'max_responsable_valeur' => $this->getMaxResponsableValeur($cadeaux),

            // Distribution par type
            'types_distribution' => $this->getTypesDistribution($cadeaux),

            // Tendance annuelle sur 3 ans
            'yearly_trend' => $this->getYearlyTrend($year),

            // Déclarations par mois
            'declarations_par_mois' => $total_declarations > 0 ? round($total_declarations / 12, 1) : 0,

            // Taux de conformité (acceptés + refusés définis)
            'taux_conformite' => $total_declarations > 0
                ? round((($acceptes + $refuses) / $total_declarations) * 100, 1)
                : 0,

            // Avec documents
            'avec_document' => $cadeaux->filter(function ($cadeau) {
                return !empty($cadeau->document);
            })->count(),

            // Croissance mensuelle moyenne
            'croissance_mensuelle' => $this->calculateMonthlyGrowth($cadeaux),

            // Plage de dates
            'date_range' => [
                'start' => $cadeaux->min('date')?->format('d/m/Y') ?? '01/01/' . $year,
                'end' => $cadeaux->max('date')?->format('d/m/Y') ?? '31/12/' . $year
            ]
        ];

        return $stats;
    }

    private function calculateMedian($array)
    {
        if (empty($array)) {
            return 0;
        }

        sort($array);
        $count = count($array);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return ($array[$middle - 1] + $array[$middle]) / 2;
        } else {
            return $array[$middle];
        }
    }

    private function calculateEvolution($currentYear)
    {
        $previousYear = $currentYear - 1;
        $prevStart = Carbon::createFromDate($previousYear, 1, 1)->startOfDay();
        $prevEnd = Carbon::createFromDate($previousYear, 12, 31)->endOfDay();

        $currentCount = CadeauInvitation::whereBetween('date', [
            Carbon::createFromDate($currentYear, 1, 1)->startOfDay(),
            Carbon::createFromDate($currentYear, 12, 31)->endOfDay()
        ])->count();

        $previousCount = CadeauInvitation::whereBetween('date', [$prevStart, $prevEnd])->count();

        if ($previousCount > 0) {
            return round((($currentCount - $previousCount) / $previousCount) * 100, 1);
        }

        return 0;
    }

    private function getMonthlyData($cadeaux, $year)
    {
        $monthlyData = collect(range(1, 12))->mapWithKeys(function ($month) use ($cadeaux, $year) {
            $monthCadeaux = $cadeaux->filter(function ($item) use ($month, $year) {
                return $item->date &&
                    $item->date->month == $month &&
                    $item->date->year == $year;
            });

            return [
                $month => [
                    'counts' => $monthCadeaux->count(),
                    'valeurs' => $monthCadeaux->sum('valeurs') ?? 0,
                    'acceptes' => $monthCadeaux->where('action_prise', 'accepté')->count()
                ]
            ];
        });

        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        return [
            'labels' => $months,
            'counts' => $monthlyData->pluck('counts')->toArray(),
            'valeurs' => $monthlyData->pluck('valeurs')->toArray(),
            'acceptes' => $monthlyData->pluck('acceptes')->toArray()
        ];
    }

    private function getTopCadeaux($cadeaux, $limit = 5)
    {
        return $cadeaux->sortByDesc('valeurs')
            ->take($limit)
            ->map(function ($cadeau) {
                return [
                    'nom' => $cadeau->nom,
                    'valeurs' => $cadeau->valeurs ?? 0,
                    'cadeau_hospitalite' => $cadeau->cadeau_hospitalite,
                    'action_prise' => $cadeau->action_prise,
                    'date_formatted' => $cadeau->date?->format('d/m/Y') ?? 'Date non spécifiée'
                ];
            })
            ->values()
            ->toArray();
    }

    private function getTopResponsables($cadeaux, $limit = 6)
    {
        $responsables = [];

        foreach ($cadeaux as $cadeau) {
            $responsableId = $cadeau->responsable_id;

            if ($responsableId) {
                if (!isset($responsables[$responsableId])) {
                    $user = User::find($responsableId);
                    $responsables[$responsableId] = [
                        'id' => $responsableId,
                        'name' => $user ? trim($user->nom . ' ' . $user->prenom) : "User #$responsableId",
                        'declaration_count' => 0,
                        'accepte_count' => 0,
                        'refuse_count' => 0,
                        'total_valeurs' => 0
                    ];
                }

                $responsables[$responsableId]['declaration_count']++;
                $responsables[$responsableId]['total_valeurs'] += $cadeau->valeurs ?? 0;

                if ($cadeau->action_prise === 'accepté') {
                    $responsables[$responsableId]['accepte_count']++;
                } elseif ($cadeau->action_prise === 'refusé') {
                    $responsables[$responsableId]['refuse_count']++;
                }
            }
        }

        return collect($responsables)
            ->sortByDesc('total_valeurs')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getMaxResponsableValeur($cadeaux)
    {
        $responsables = [];

        foreach ($cadeaux as $cadeau) {
            $responsableId = $cadeau->responsable_id;

            if ($responsableId) {
                if (!isset($responsables[$responsableId])) {
                    $responsables[$responsableId] = 0;
                }

                $responsables[$responsableId] += $cadeau->valeurs ?? 0;
            }
        }

        return !empty($responsables) ? max($responsables) : 1;
    }

    private function getTypesDistribution($cadeaux)
    {
        $types = $cadeaux->groupBy('cadeau_hospitalite')
            ->map(function ($group, $type) use ($cadeaux) {
                $typeLabel = $type ?: 'Non spécifié';
                $icon = match (strtolower($typeLabel)) {
                    'cadeau' => 'fa-gift',
                    'invitation' => 'fa-calendar-alt',
                    'hospitalité' => 'fa-glass-cheers',
                    default => 'fa-question'
                };

                $color = match (strtolower($typeLabel)) {
                    'cadeau' => '#f59e0b',
                    'invitation' => '#3b82f6',
                    'hospitalité' => '#10b981',
                    default => '#6b7280'
                };

                $total = $cadeaux->count();
                $percentage = $total > 0 ? round(($group->count() / $total) * 100, 1) : 0;

                return [
                    'label' => $typeLabel,
                    'count' => $group->count(),
                    'total_valeurs' => $group->sum('valeurs') ?? 0,
                    'percentage' => $percentage,
                    'icon' => $icon,
                    'color' => $color
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->toArray();

        // Limiter à 3 types maximum
        return array_slice($types, 0, 3);
    }

    private function getYearlyTrend($currentYear, $years = 3)
    {
        $trendData = [
            'labels' => [],
            'totals' => [],
            'valeurs' => []
        ];

        for ($i = $years - 1; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $start = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $end = Carbon::createFromDate($year, 12, 31)->endOfDay();

            $yearCadeaux = CadeauInvitation::whereBetween('date', [$start, $end])->get();

            $trendData['labels'][] = (string)$year;
            $trendData['totals'][] = $yearCadeaux->count();
            $trendData['valeurs'][] = $yearCadeaux->sum('valeurs') / 1000; // En milliers
        }

        return $trendData;
    }

    private function calculateMonthlyGrowth($cadeaux)
    {
        if ($cadeaux->count() < 2) {
            return 0;
        }

        $monthlyCounts = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyCounts[] = $cadeaux->filter(function ($c) use ($month) {
                return $c->date && $c->date->month == $month;
            })->count();
        }

        // Calculer la croissance moyenne mensuelle
        $growthRates = [];
        for ($i = 1; $i < count($monthlyCounts); $i++) {
            if ($monthlyCounts[$i - 1] > 0) {
                $growthRates[] = (($monthlyCounts[$i] - $monthlyCounts[$i - 1]) / $monthlyCounts[$i - 1]) * 100;
            }
        }

        return !empty($growthRates) ? round(array_sum($growthRates) / count($growthRates), 1) : 0;
    }
}
