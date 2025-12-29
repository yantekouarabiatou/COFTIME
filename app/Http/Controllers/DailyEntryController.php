<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Dossier;
use App\Exports\TimesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;

class DailyEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::orderBy('nom')->get();

        $query = DailyEntry::with(['user', 'timeEntries.dossier.client']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('jour', [$request->date_debut, $request->date_fin]);
        }

        $dailyEntries = $query->latest('jour')->paginate(50);

        return view('pages.daily-entries.index', compact('dailyEntries', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::orderBy('nom')->get();
        $dossiers = Dossier::with('client')->orderBy('nom')->get();

        return view('pages.daily-entries.create', compact('users', 'dossiers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jour' => 'required|date',
            'heures_theoriques' => 'required|numeric|min:0|max:24',
            'commentaire' => 'nullable|string',

            'time_entries' => 'required|array|min:1',
            'time_entries.*.dossier_id' => 'required|exists:dossiers,id',
            'time_entries.*.heure_debut' => 'required',
            'time_entries.*.heure_fin' => 'required|after:time_entries.*.heure_debut',
            'time_entries.*.heures' => 'required|numeric|min:0.25',
            'time_entries.*.travaux' => 'nullable|string',
        ]);

        $date = Carbon::parse($request->jour);

        if (DailyEntry::where('user_id', $request->user_id)->where('jour', $date)->exists()) {
            Alert::error('Erreur', 'Une entrée existe déjà pour cet employé à cette date.');
            return back()->withInput();
        }

        $dailyEntry = DailyEntry::create([
            'user_id' => $request->user_id,
            'jour' => $date,
            'heures_theoriques' => $request->heures_theoriques,
            'heures_totales' => collect($request->time_entries)->sum('heures'),
            'is_weekend' => $date->isWeekend(),
            'is_holiday' => false,
            'commentaire' => $request->commentaire,
        ]);

        foreach ($request->time_entries as $te) {
            TimeEntry::create([
                'daily_entry_id' => $dailyEntry->id,
                'user_id' => $request->user_id,
                'dossier_id' => $te['dossier_id'],
                'heure_debut' => $te['heure_debut'],
                'heure_fin' => $te['heure_fin'],
                'heures' => $te['heures'],
                'travaux' => $te['travaux'] ?? null,
            ]);
        }

        Alert::success('Succès !', 'Feuille de temps créée avec succès.');

        return redirect()->route('daily-entries.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyEntry $dailyEntry)
    {
        $this->authorize('view', $dailyEntry); // Optionnel : si tu as une Policy

        $dailyEntry->load('user', 'timeEntries.dossier.client');

        return view('pages.daily-entries.show', compact('dailyEntry'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyEntry $dailyEntry)
    {
        $this->authorize('update', $dailyEntry);

        $users = User::orderBy('nom')->get();
        $dossiers = Dossier::with('client')->orderBy('nom')->get();

        $dailyEntry->load('timeEntries');

        return view('pages.daily-entries.edit', compact('dailyEntry', 'users', 'dossiers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyEntry $dailyEntry)
    {
        $this->authorize('update', $dailyEntry);

        $request->validate([
            'heures_theoriques' => 'required|numeric|min:0|max:24',
            'commentaire' => 'nullable|string',

            'time_entries' => 'required|array|min:1',
            'time_entries.*.id' => 'nullable|exists:time_entries,id,daily_entry_id,' . $dailyEntry->id,
            'time_entries.*.dossier_id' => 'required|exists:dossiers,id',
            'time_entries.*.heure_debut' => 'required',
            'time_entries.*.heure_fin' => 'required|after:time_entries.*.heure_debut',
            'time_entries.*.heures' => 'required|numeric|min:0.25',
            'time_entries.*.travaux' => 'nullable|string',
        ]);

        // Mise à jour des champs généraux
        $dailyEntry->update([
            'heures_theoriques' => $request->heures_theoriques,
            'commentaire' => $request->commentaire,
        ]);

        // Suppression des anciennes time entries non présentes
        $existingIds = collect($request->time_entries)->pluck('id')->filter()->toArray();
        $dailyEntry->timeEntries()->whereNotIn('id', $existingIds)->delete();

        // Mise à jour / création des time entries
        foreach ($request->time_entries as $te) {
            $data = [
                'dossier_id' => $te['dossier_id'],
                'heure_debut' => $te['heure_debut'],
                'heure_fin' => $te['heure_fin'],
                'heures' => $te['heures'],
                'travaux' => $te['travaux'] ?? null,
            ];

            if (isset($te['id'])) {
                TimeEntry::find($te['id'])->update($data);
            } else {
                $dailyEntry->timeEntries()->create(array_merge($data, ['user_id' => $dailyEntry->user_id]));
            }
        }

        // Le total heures_totales sera mis à jour automatiquement par l'Observer

        Alert::success('Mis à jour !', 'Feuille de temps modifiée avec succès.');

        return redirect()->route('daily-entries.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyEntry $dailyEntry)
    {
        $this->authorize('delete', $dailyEntry);

        $dailyEntry->delete();

        Alert::success('Supprimé !', 'Feuille de temps supprimée avec succès.');

        return back();
    }

    /**
     * Export Excel par période
     */
    public function export(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        Alert::success('Export lancé', 'Votre fichier Excel est en cours de génération...');

        return Excel::download(
            new TimesExport($request->date_debut, $request->date_fin),
            'temps_' . Carbon::parse($request->date_debut)->format('Y-m-d') . '_au_' . Carbon::parse($request->date_fin)->format('Y-m-d') . '.xlsx'
        );
    }
}
