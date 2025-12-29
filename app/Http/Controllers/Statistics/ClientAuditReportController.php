<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ClientAudit;
use App\Models\User;
use Carbon\Carbon;

class ClientAuditReportController extends Controller
{
    public function annual()
    {
        $currentYear = date('Y');
        $startDate = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();

        // Récupérer tous les clients de l'année
        $clients = ClientAudit::whereBetween('created_at', [$startDate, $endDate])->get();

        // Calculer les statistiques
        $stats = $this->calculateStats($clients, $currentYear);

        return view('pages.statistics.clients', [
            'stats' => $stats,
            'currentYear' => $currentYear
        ]);
    }

    private function calculateStats($clients, $year)
    {
        // Compter par statut - CORRECTION ICI
        $clients_actifs = $clients->where('statut', 'actif')->count();
        $clients_en_cours = $clients->where('statut', 'en_cours')->count();
        $clients_inactifs = $clients->where('statut', 'inactif')->count();

        // Correction: utiliser filter() au lieu de orWhere()
        $clients_sans_statut = $clients->filter(function ($client) {
            return empty($client->statut) || trim($client->statut) === '';
        })->count();

        // Calculer les totaux financiers par statut
        $frais_actifs = $clients->where('statut', 'actif')->sum('total_frais');
        $frais_en_cours = $clients->where('statut', 'en_cours')->sum('total_frais');
        $frais_inactifs = $clients->where('statut', 'inactif')->sum('total_frais');

        // Correction ici aussi
        $frais_sans_statut = $clients->filter(function ($client) {
            return empty($client->statut) || trim($client->statut) === '';
        })->sum('total_frais');

        $total_clients = $clients->count();

        $stats = [
            // Totaux généraux
            'total_clients' => $total_clients,
            'total_frais' => $clients->sum('total_frais'),
            'total_frais_audit' => $clients->sum('frais_audit'),
            'total_frais_autres' => $clients->sum('frais_autres'),

            // Par statut - quantités
            'clients_actifs' => $clients_actifs,
            'clients_en_cours' => $clients_en_cours,
            'clients_inactifs' => $clients_inactifs,
            'clients_sans_statut' => $clients_sans_statut,

            // Par statut - montants
            'frais_actifs' => $frais_actifs,
            'frais_en_cours' => $frais_en_cours,
            'frais_inactifs' => $frais_inactifs,
            'frais_sans_statut' => $frais_sans_statut,

            // Taux par statut
            'taux_actifs' => $total_clients > 0 ? round(($clients_actifs / $total_clients) * 100, 1) : 0,
            'taux_en_cours' => $total_clients > 0 ? round(($clients_en_cours / $total_clients) * 100, 1) : 0,
            'taux_inactifs' => $total_clients > 0 ? round(($clients_inactifs / $total_clients) * 100, 1) : 0,
            'taux_sans_statut' => $total_clients > 0 ? round(($clients_sans_statut / $total_clients) * 100, 1) : 0,

            // Moyennes
            'frais_moyens' => $total_clients > 0 ? $clients->avg('total_frais') : 0,
            'frais_moyens_audit' => $total_clients > 0 ? $clients->avg('frais_audit') : 0,
            'frais_moyens_autres' => $total_clients > 0 ? $clients->avg('frais_autres') : 0,

            // Maxima
            'max_frais_audit' => $clients->max('frais_audit') ?? 1,
            'max_frais_autres' => $clients->max('frais_autres') ?? 1,

            // Ratios
            'ratio_frais_autres' => $clients->sum('total_frais') > 0
                ? round(($clients->sum('frais_autres') / $clients->sum('total_frais')) * 100, 1)
                : 0,

            // Évolution
            'evolution_percentage' => $this->calculateEvolution($year),

            // Distribution par statut pour graphique
            'status_distribution' => [
                'labels' => ['Actifs', 'En cours', 'Inactifs', 'Sans statut'],
                'data' => [$clients_actifs, $clients_en_cours, $clients_inactifs, $clients_sans_statut]
            ],

            // Données mensuelles
            'monthly_data' => $this->getMonthlyData($clients, $year),

            // Top clients
            'top_clients' => $this->getTopClients($clients),

            // Top responsables
            'top_responsables' => $this->getTopResponsables($clients),
            'max_responsable_frais' => $this->getMaxResponsableFrais($clients),

            // Répartition géographique
            'top_locations' => $this->getTopLocations($clients),
            'max_location_count' => $this->getMaxLocationCount($clients),

            // Données géographiques pour graphique
            'geographic_data' => $this->getGeographicData($clients),

            // Tendance annuelle sur 3 ans
            'yearly_trend' => $this->getYearlyTrend($year),

            // Nouveaux clients de l'année
            'nouveaux_clients' => $clients->filter(function ($client) use ($year) {
                return $client->created_at &&
                    $client->created_at->year == $year;
            })->count(),

            // Taux de fidélité (clients actifs par rapport au total)
            'taux_fidelite' => $total_clients > 0 ? round(($clients_actifs / $total_clients) * 100, 1) : 0,

            // Clients avec documents
            'clients_avec_document' => $clients->filter(function ($client) {
                return !empty($client->document);
            })->count(),

            // Croissance mensuelle moyenne
            'croissance_mensuelle' => $this->calculateMonthlyGrowth($clients),

            // Plage de dates
            'date_range' => [
                'start' => $clients->min('created_at')?->format('d/m/Y') ?? '01/01/' . $year,
                'end' => $clients->max('created_at')?->format('d/m/Y') ?? '31/12/' . $year
            ]
        ];

        return $stats;
    }

    private function calculateEvolution($currentYear)
    {
        $previousYear = $currentYear - 1;
        $prevStart = Carbon::createFromDate($previousYear, 1, 1)->startOfDay();
        $prevEnd = Carbon::createFromDate($previousYear, 12, 31)->endOfDay();

        $currentCount = ClientAudit::whereYear('created_at', $currentYear)->count();
        $previousCount = ClientAudit::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        if ($previousCount > 0) {
            return round((($currentCount - $previousCount) / $previousCount) * 100, 1);
        }

        return 0;
    }

    private function getMonthlyData($clients, $year)
    {
        $monthlyData = collect(range(1, 12))->mapWithKeys(function ($month) use ($clients, $year) {
            $monthClients = $clients->filter(
                fn($item) =>
                $item->created_at &&
                    $item->created_at->month == $month &&
                    $item->created_at->year == $year
            );

            return [
                $month => [
                    'nouveaux' => $monthClients->count(),
                    'actifs' => $monthClients->where('statut', 'actif')->count(),
                    'frais_totaux' => $monthClients->sum('total_frais')
                ]
            ];
        });

        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        return [
            'labels' => $months,
            'nouveaux' => $monthlyData->pluck('nouveaux')->toArray(),
            'actifs' => $monthlyData->pluck('actifs')->toArray(),
            'frais_totaux' => $monthlyData->pluck('frais_totaux')->toArray()
        ];
    }

    private function getTopClients($clients, $limit = 5)
{
    return $clients->sortByDesc('total_frais')
        ->take($limit)
        ->map(function ($client) {
            // Déterminer la couleur en fonction du statut
            $statutColor = match($client->statut) {
                'actif' => 'success',
                'en_cours' => 'primary',
                'inactif' => 'secondary',
                default => 'warning'
            };
            
            // Déterminer le statut formaté
            $statutFormatted = match($client->statut) {
                'actif' => 'Actif',
                'en_cours' => 'En cours',
                'inactif' => 'Inactif',
                default => 'Non défini'
            };
            
            return [
                'nom_client' => $client->nom_client,
                'frais_audit' => $client->frais_audit ?? 0,
                'frais_autres' => $client->frais_autres ?? 0,
                'total_frais' => $client->total_frais,
                'siege_social' => $client->siege_social,
                'statut' => $client->statut,
                'statut_formatted' => $statutFormatted,
                'statut_color' => $statutColor
            ];
        })
        ->values()
        ->toArray();
}

    private function getTopResponsables($clients, $limit = 6)
    {
        $responsables = [];

        foreach ($clients as $client) {
            $responsableId = $client->responsable_id;

            if ($responsableId) {
                if (!isset($responsables[$responsableId])) {
                    $user = User::find($responsableId);
                    $responsables[$responsableId] = [
                        'id' => $responsableId,
                        'name' => $user ? trim($user->nom . ' ' . $user->prenom) : "User #$responsableId",
                        'client_count' => 0,
                        'actif_count' => 0,
                        'en_cours_count' => 0,
                        'total_frais' => 0
                    ];
                }

                $responsables[$responsableId]['client_count']++;
                $responsables[$responsableId]['total_frais'] += $client->total_frais;

                if ($client->statut === 'actif') {
                    $responsables[$responsableId]['actif_count']++;
                } elseif ($client->statut === 'en_cours') {
                    $responsables[$responsableId]['en_cours_count']++;
                }
            }
        }

        return collect($responsables)
            ->sortByDesc('total_frais')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getMaxResponsableFrais($clients)
    {
        $responsables = [];

        foreach ($clients as $client) {
            $responsableId = $client->responsable_id;

            if ($responsableId) {
                if (!isset($responsables[$responsableId])) {
                    $responsables[$responsableId] = 0;
                }

                $responsables[$responsableId] += $client->total_frais;
            }
        }

        return !empty($responsables) ? max($responsables) : 1;
    }

    private function getTopLocations($clients, $limit = 5)
    {
        // Extraire les villes des adresses (simplifié)
        $locations = [];

        foreach ($clients as $client) {
            if (!empty($client->siege_social)) {
                // Essayer d'extraire la ville (très basique)
                $ville = $this->extractCity($client->siege_social);

                if (!isset($locations[$ville])) {
                    $locations[$ville] = [
                        'ville' => $ville,
                        'count' => 0,
                        'actif_count' => 0,
                        'total_frais' => 0
                    ];
                }

                $locations[$ville]['count']++;
                $locations[$ville]['total_frais'] += $client->total_frais;

                if ($client->statut === 'actif') {
                    $locations[$ville]['actif_count']++;
                }
            }
        }

        return collect($locations)
            ->sortByDesc('count')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function extractCity($address)
    {
        // Logique simplifiée d'extraction de ville
        $commonCities = ['Abidjan', 'Yamoussoukro', 'Bouaké', 'Daloa', 'San Pedro', 'Korhogo', 'Anyama'];

        foreach ($commonCities as $city) {
            if (stripos($address, $city) !== false) {
                return $city;
            }
        }

        return 'Autre';
    }

    private function getMaxLocationCount($clients)
    {
        $locations = [];

        foreach ($clients as $client) {
            if (!empty($client->siege_social)) {
                $ville = $this->extractCity($client->siege_social);

                if (!isset($locations[$ville])) {
                    $locations[$ville] = 0;
                }

                $locations[$ville]++;
            }
        }

        return !empty($locations) ? max($locations) : 1;
    }

    private function getGeographicData($clients)
    {
        $locations = $this->getTopLocations($clients, 10);

        return [
            'labels' => collect($locations)->pluck('ville')->toArray(),
            'counts' => collect($locations)->pluck('count')->toArray()
        ];
    }

    private function getYearlyTrend($currentYear, $years = 3)
    {
        $trendData = [
            'labels' => [],
            'totals' => [],
            'actifs' => []
        ];

        for ($i = $years - 1; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $start = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $end = Carbon::createFromDate($year, 12, 31)->endOfDay();

            $yearClients = ClientAudit::whereBetween('created_at', [$start, $end])->get();

            $trendData['labels'][] = $year;
            $trendData['totals'][] = $yearClients->count();
            $trendData['actifs'][] = $yearClients->where('statut', 'actif')->count();
        }

        return $trendData;
    }

    private function calculateMonthlyGrowth($clients)
    {
        if ($clients->count() < 2) {
            return 0;
        }

        $monthlyCounts = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyCounts[] = $clients->filter(
                fn($c) =>
                $c->created_at && $c->created_at->month == $month
            )->count();
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
