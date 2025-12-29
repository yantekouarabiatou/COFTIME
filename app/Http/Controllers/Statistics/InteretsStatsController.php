<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use App\Models\Interet;
use App\Models\User;
use Illuminate\Http\Request;

class InteretsStatsController extends Controller
{
    public function index()
    {
        $years = range(now()->year, now()->year - 10);
        $stats = $this->calculateStats($years);

        return view('pages.statistics.interets', compact('stats', 'years'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'start_year' => 'required|integer',
            'end_year'   => 'required|integer|gte:start_year',
        ]);

        $years = range($request->end_year, $request->start_year);

        return response()->json($this->calculateStats($years));
    }

    private function calculateStats(array $years)
    {
        $labels            = [];
        $totalParAnnee     = [];
        $actifsParAnnee    = [];
        $traitesParAnnee   = [];

        // Top responsables (par nombre d'intérêts assignés)
        $topResponsables = [];

        foreach ($years as $year) {
            $start = "$year-01-01";
            $end   = "$year-12-31 23:59:59";

            $labels[] = $year;

            $interets = Interet::whereBetween('date_Notification', [$start, $end])
                                ->orWhereNull('date_Notification')
                                ->get();

            $total = $interets->count();
            $actifs = $interets->whereIn('etat_interet', ['Actif', 'En cours'])->count();
            $traites = $interets->where('etat_interet', 'Traité')->count();

            $totalParAnnee[]  = $total;
            $actifsParAnnee[] = $actifs;
            $traitesParAnnee[]= $traites;

            // On cumule pour le top responsables
            foreach ($interets as $i) {
                if ($i->responsable_id) {
                    $topResponsables[$i->responsable_id] = ($topResponsables[$i->responsable_id] ?? 0) + 1;
                }
            }
        }

        // Transformation du top en tableau avec noms
        $topResponsables = User::whereIn('id', array_keys($topResponsables ?? []))
            ->pluck('prenom', 'id')
            ->map(fn($prenom, $id) => $prenom . ' ' . User::find($id)?->nom)
            ->sortDesc()
            ->take(8)
            ->mapWithKeys(fn($nom, $id) => [$nom => $topResponsables[$id] ?? 0])
            ->toArray();

        $totalInterets = array_sum($totalParAnnee);
        $totalActifs   = array_sum($actifsParAnnee);
        $tauxActifs    = $totalInterets > 0 ? round($totalActifs / $totalInterets * 100, 1) : 0;

        return [
            'labels'             => $labels,
            'total_par_annee'    => $totalParAnnee,
            'actifs_par_annee'    => $actifsParAnnee,
            'traites_par_annee'  => $traitesParAnnee,
            'top_responsables'   => $topResponsables,
            'total_interets'     => $totalInterets,
            'total_actifs'       => $totalActifs,
            'taux_actifs'        => $tauxActifs . '%',
        ];
    }
}