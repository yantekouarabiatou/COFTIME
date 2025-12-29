<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    public function mensuel(Request $request, $userId = null, $year = null, $month = null)
    {
        $now = Carbon::now();
        $year = $year ?? $now->year;
        $month = $month ?? $now->month;

        $date = Carbon::create($year, $month, 1);
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $users = User::orderBy('nom')->get();

        if ($userId) {
            $selectedUser = User::findOrFail($userId);
            $dailyEntries = DailyEntry::with('timeEntries.dossier.client')
                ->where('user_id', $userId)
                ->whereBetween('jour', [$start, $end])
                ->orderBy('jour')
                ->get();
            $title = "Rapport mensuel - {$selectedUser->full_name} - {$date->translatedFormat('F Y')}";
        } else {
            $dailyEntries = DailyEntry::with('user', 'timeEntries.dossier.client')
                ->whereBetween('jour', [$start, $end])
                ->orderBy('jour')
                ->get()
                ->groupBy('user_id');
            $title = "Rapport mensuel global - {$date->translatedFormat('F Y')}";
        }

        return view('pages.rapports.mensuel', compact('dailyEntries', 'users', 'year', 'month', 'title', 'selectedUser'));
    }
}
