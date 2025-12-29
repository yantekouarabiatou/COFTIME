<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plainte;
use App\Models\ClientAudit;
use App\Models\CadeauInvitation;
use App\Models\Interet;
use App\Models\Independance;
use App\Models\User;
use Carbon\Carbon;

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
        // Totaux généraux
        $totals = [
            'plaintes' => Plainte::count(),
            'rca' => ClientAudit::count(),
            'cadeaux' => CadeauInvitation::count(),
            'conflits' => Interet::count(),
            'independances' => Independance::count(),
        ];

        // Statistiques hebdomadaires (7 derniers jours)
        $weekStart = now()->subDays(7);
        $weeklyStats = [
            'plaintes' => Plainte::where('created_at', '>=', $weekStart)->count(),
            'rca' => ClientAudit::where('created_at', '>=', $weekStart)->count(),
            'cadeaux' => CadeauInvitation::where('created_at', '>=', $weekStart)->count(),
            'conflits' => Interet::where('created_at', '>=', $weekStart)->count(),
            'independances' => Independance::where('created_at', '>=', $weekStart)->count(),
        ];

        // Statistiques du mois précédent (pour comparaison)
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $lastMonthStats = [
            'plaintes' => Plainte::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
            'rca' => ClientAudit::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
            'cadeaux' => CadeauInvitation::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
            'conflits' => Interet::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
            'independances' => Independance::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
        ];

        // Statistiques mensuelles (mois en cours)
        $monthlyStats = [
            'plaintes' => Plainte::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count(),
            'rca' => ClientAudit::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count(),
            'cadeaux' => CadeauInvitation::whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count(),
            'conflits' => Interet::whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->count(),
            'independances' => Independance::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)
                                         ->count(),
        ];

        // Calculer les pourcentages d'évolution (par rapport au mois précédent)
        $percentages = [
            'plaintes' => $this->calculatePercentage($monthlyStats['plaintes'], $lastMonthStats['plaintes']),
            'rca' => $this->calculatePercentage($monthlyStats['rca'], $lastMonthStats['rca']),
            'cadeaux' => $this->calculatePercentage($monthlyStats['cadeaux'], $lastMonthStats['cadeaux']),
            'conflits' => $this->calculatePercentage($monthlyStats['conflits'], $lastMonthStats['conflits']),
            'independances' => $this->calculatePercentage($monthlyStats['independances'], $lastMonthStats['independances']),
        ];

        // Données des 30 derniers jours
        $last30days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateLabel = $date->format('d/m');
            
            $last30days->put($dateLabel, [
                'plaintes' => Plainte::whereDate('created_at', $date)->count(),
                'rca' => ClientAudit::whereDate('created_at', $date)->count(),
                'cadeaux' => CadeauInvitation::whereDate('created_at', $date)->count(),
                'conflits' => Interet::whereDate('created_at', $date)->count(),
                'independances' => Independance::whereDate('created_at', $date)->count(),
            ]);
        }

        // Top 5 utilisateurs actifs
        $topUsers = User::select('id', 'prenom', 'nom')
            ->get()
            ->map(function ($user) {
                // Compter manuellement pour éviter les erreurs de relation
                $plainteCount = Plainte::where('user_id', $user->id)->count();
                $rcaCount = ClientAudit::where('user_id', $user->id)->count();
                $cadeauxCount = CadeauInvitation::where('user_id', $user->id)->count();
                $conflitsCount = Interet::where('user_id', $user->id)->count();
                $independancesCount = Independance::where('user_id', $user->id)->count();
                
                $user->total = $plainteCount + $rcaCount + $cadeauxCount + $conflitsCount + $independancesCount;
                return $user;
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        return response()->json([
            'totals' => $totals,
            'weekly' => $weeklyStats,
            'monthly' => $monthlyStats,
            'percentages' => $percentages,
            'last30days' => [
                'dates' => $last30days->keys()->toArray(),
                'plaintes' => $last30days->pluck('plaintes')->toArray(),
                'rca' => $last30days->pluck('rca')->toArray(),
                'cadeaux' => $last30days->pluck('cadeaux')->toArray(),
                'conflits' => $last30days->pluck('conflits')->toArray(),
                'independances' => $last30days->pluck('independances')->toArray()
            ],
            'topUsers' => [
                'names' => $topUsers->pluck('prenom')->map(fn($n) => ucfirst($n))->toArray(),
                'counts' => $topUsers->pluck('total')->toArray(),
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
        
        return response()->json([
            'user' => [
                'name' => $user->prenom . ' ' . $user->nom,
                'email' => $user->email,
            ],
            'stats' => [
                'plaintes' => Plainte::where('user_id', $userId)->count(),
                'rca' => ClientAudit::where('user_id', $userId)->count(),
                'cadeaux' => CadeauInvitation::where('user_id', $userId)->count(),
                'conflits' => Interet::where('user_id', $userId)->count(),
                'independances' => Independance::where('user_id', $userId)->count(),
            ]
        ]);
    }

    /**
     * Exporter les statistiques
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'excel'); // excel ou pdf
        
        // Logique d'exportation à implémenter
        // Utiliser Laravel Excel ou DomPDF
        
        return response()->json([
            'message' => 'Export en cours de développement',
            'type' => $type
        ]);
    }
}