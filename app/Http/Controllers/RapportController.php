<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    public function mensuel(Request $request)
    {
        // Récupérer les paramètres depuis la requête
        $userId = $request->get('user_id');
        $dateFilter = $request->get('date_filter');

        // Déterminer l'année et le mois
        if ($dateFilter) {
            list($year, $month) = explode('-', $dateFilter);
        } else {
            $now = Carbon::now();
            $year = $now->year;
            $month = $now->month;
        }

        $date = Carbon::create($year, $month, 1);
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $users = User::orderBy('nom')->get();

        // Initialiser $selectedUser à null par défaut
        $selectedUser = null;

        // Filtrer selon l'utilisateur
        if ($userId) {
            $selectedUser = User::findOrFail($userId);
            $dailyEntries = DailyEntry::with('timeEntries.dossier.client')
                ->where('user_id', $userId)
                ->whereBetween('jour', [$start, $end])
                ->orderBy('jour')
                ->get();
            $title = "Rapport mensuel - {$selectedUser->nom} - {$date->translatedFormat('F Y')}";
        } else {
            // Pour un rapport global, regrouper par utilisateur
            $dailyEntries = DailyEntry::with('user', 'timeEntries.dossier.client')
                ->whereBetween('jour', [$start, $end])
                ->orderBy('user_id')
                ->orderBy('jour')
                ->get()
                ->groupBy('user_id');
            $title = "Rapport mensuel global - {$date->translatedFormat('F Y')}";
        }

        return view('pages.rapports.mensuel', compact(
            'dailyEntries',
            'users',
            'year',
            'month',
            'title',
            'selectedUser'
        ));
    }
}
