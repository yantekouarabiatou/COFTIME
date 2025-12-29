<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use App\Models\Plainte;
use App\Models\Assignation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PlaintesStatsController extends Controller
{
    public function index()
    {
        $years = range(now()->year, now()->year - 10);
        $stats = $this->calculateStats($years);

        return view('pages.statistics.plaintes', compact('stats', 'years'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'start_year' => 'required|integer',
            'end_year'   => 'required|integer|gte:start_year',
        ]);

        $start = $request->start_year;
        $end   = $request->end_year;
        $years = range($end, $start);

        return response()->json($this->calculateStats($years));
    }

    private function calculateStats(array $years)
    {
        $labels = [];
        $plaintesTotal = [];
        $plaintesResolues = [];
        $plaintesEnCours = [];
        $assignationsCount = [];
        $topResponsables = [];

        foreach ($years as $year) {
            $start = "$year-01-01";
            $end   = "$year-12-31 23:59:59";

            $labels[] = $year;

            // Plaintes
            $total = Plainte::whereBetween('created_at', [$start, $end])->count();
            $resolues = Plainte::where('etat_plainte', 'Résolue')
                                ->whereBetween('created_at', [$start, $end])->count();
            $enCours = Plainte::where('etat_plainte', 'En cours')
                               ->whereBetween('created_at', [$start, $end])->count();

            $plaintesTotal[] = $total;
            $plaintesResolues[] = $resolues;
            $plaintesEnCours[] = $enCours;

            // Assignations
            $assignationsCount[] = Assignation::whereBetween('created_at', [$start, $end])->count();
        }

        // Top 5 responsables sur toute la période
        $topResponsables = Assignation::with('user')
            ->select('user_id')
            ->selectRaw('count(*) as total')
            ->whereIn('created_at', function ($q) use ($years) {
                $q->selectRaw('created_at')
                  ->from('assignations')
                  ->whereYear('created_at', '>=', min($years))
                  ->whereYear('created_at', '<=', max($years));
            })
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->pluck('total', 'user.nom');

        // Délai moyen de traitement (en jours) pour les plaintes résolues
        $avgDays = Plainte::where('etat_plainte', 'Résolue')
            ->whereYear('created_at', '>=', min($years))
            ->whereYear('created_at', '<=', max($years))
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');

        $tauxResolution = !empty($plaintesTotal)
            ? round(collect($plaintesResolues)->sum() / collect($plaintesTotal)->sum() * 100, 1)
            : 0;

        return [
            'labels' => $labels,
            'plaintes_total' => $plaintesTotal,
            'plaintes_resolues' => $plaintesResolues,
            'plaintes_en_cours' => $plaintesEnCours,
            'assignations' => $assignationsCount,
            'top_responsables' => $topResponsables,
            'taux_resolution' => $tauxResolution . '%',
            'delai_moyen_jours' => round($avgDays ?? 0, 1),
            'total_plaintes' => array_sum($plaintesTotal),
            'total_assignations' => array_sum($assignationsCount),
        ];
    }
}