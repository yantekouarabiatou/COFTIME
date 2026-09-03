<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use App\Models\WeeklyValidation;
use App\Models\User;
use App\Models\Dossier;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\TimeEntry;
use App\Exports\DailyEntriesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class DailyEntryController extends Controller
{

    /**
     * Afficher la liste des feuilles de temps
     */ 
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = DailyEntry::with(['user', 'timeEntries.dossier.client']);

        $mois = $request->filled('mois')
            ? Carbon::parse($request->mois . '-01')
            : Carbon::now()->startOfMonth();

        $query->whereBetween('jour', [
            $mois->copy()->startOfMonth(),
            $mois->copy()->endOfMonth(),
        ]);

        $mineOnly = $request->boolean('mine');

        if ($mineOnly) {
            // Vue "Mes listes de temps" : uniquement mes propres feuilles, quel que soit le rôle
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole(['directeur-general', 'admin'])) {
            // Voit tout, aucun filtre
        } elseif ($user->hasRole('manager')) {
            $subordinateIds = $user->subordinates()->pluck('id')->toArray();
            $query->where(function ($q) use ($user, $subordinateIds) {
                $q->where('user_id', $user->id)
                ->orWhereIn('user_id', $subordinateIds);
            });
        } else {
            // Tout le monde : uniquement ses propres feuilles
            $query->where('user_id', $user->id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if (!$mineOnly && $request->filled('user')) {
            $requestedId = (int) $request->user;
            // Vérifier que l'utilisateur a le droit de filtrer sur cet ID
            $canFilter = $user->hasRole(['directeur-general', 'admin'])
                || ($user->hasRole('manager') && $user->isManagerOf($requestedId));

            if ($canFilter) {
                $query->where('user_id', $requestedId);
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('jour', $request->date);
        }

        $statsQuery     = clone $query;
        $totalHours     = (clone $statsQuery)->sum('heures_reelles');
        $submittedCount = (clone $statsQuery)->where('statut', 'soumis')->count();
        $validatedCount = (clone $statsQuery)->where('statut', 'validé')->count();
        $rejectedCount  = (clone $statsQuery)->where('statut', 'refusé')->count();

        $dailyEntries = $query->latest()->paginate(20)->withQueryString();

        // Liste collaborateurs pour le filtre select
        $users = collect();
        if (!$mineOnly && $user->hasRole(['directeur-general', 'admin'])) {
            $users = User::where('is_active', 1)->orderBy('prenom')->get(['id', 'prenom', 'nom']);
        } elseif (!$mineOnly && $user->hasRole('manager')) {
            $users = $user->subordinates()->where('is_active', 1)->orderBy('prenom')->get(['id', 'prenom', 'nom']);
        }

        return view('pages.daily-entries.index', compact(
            'dailyEntries', 'totalHours', 'submittedCount',
            'validatedCount', 'rejectedCount', 'users', 'mois', 'mineOnly'
        ));
    }

    // ------------------------------------------------------------------------
    // CREATE / STORE
    // ------------------------------------------------------------------------

    public function create()
    {
        $currentUser = Auth::user();

        // Si une feuille existe déjà pour aujourd'hui → rediriger vers edit
        $todayEntry = DailyEntry::where('user_id', $currentUser->id)
            ->whereDate('jour', now()->toDateString())
            ->where('est_manquant', false)
            ->first();

        if ($todayEntry) {
            return redirect()
                ->route('daily-entries.edit', $todayEntry)
                ->with('info', 'Vous avez déjà une feuille de temps pour aujourd\'hui.');
        }

        $dossiers = $this->getDossiersForUser($currentUser);
        $clients  = Client::whereIn('statut', ['actif', 'prospect'])->orderBy('nom')->get();

        return view('pages.daily-entries.create', compact('currentUser', 'dossiers', 'clients'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'                           => 'required|exists:users,id',
            'jour'                              => 'required|date',
            'heures_theoriques'                 => 'required|numeric|min:0|max:24',
            'commentaire'                       => 'nullable|string',
            'time_entries'                      => 'required|array|min:1',
            'time_entries.*.dossier_id'         => 'required|exists:dossiers,id',
            'time_entries.*.heure_debut'        => 'required|date_format:H:i',
            'time_entries.*.heure_fin'          => 'required|date_format:H:i',
            'time_entries.*.heures_reelles'     => 'required|numeric|min:0.25',
            'time_entries.*.travaux'            => 'nullable|string|max:500',
            'time_entries.*.rendu'              => 'nullable|string|max:500',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ($request->input('time_entries', []) as $index => $entry) {
                if (!empty($entry['heure_debut']) && !empty($entry['heure_fin'])
                    && strtotime($entry['heure_fin']) <= strtotime($entry['heure_debut'])) {
                    $validator->errors()->add("time_entries.{$index}.heure_fin", 'L\'heure de fin doit être après l\'heure de début.');
                }
            }
        });

        $validated = $validator->validate();

        $jour = Carbon::parse($validated['jour']);

        $dailyEntry = DailyEntry::firstOrCreate(
            ['user_id' => $validated['user_id'], 'jour' => $validated['jour']],
            [
                'heures_theoriques' => $validated['heures_theoriques'],
                'commentaire'       => $validated['commentaire'],
                'statut'            => 'soumis',
                'est_manquant'      => false,
                'semaine'           => $jour->isoWeek(),
                'annee_semaine'     => $jour->isoWeekYear(),
            ]
        );

        if (!$dailyEntry->wasRecentlyCreated) {
            $dailyEntry->update([
                'heures_theoriques' => $validated['heures_theoriques'],
                'commentaire'       => $validated['commentaire'],
                'statut'            => 'soumis',
                'est_manquant'      => false,
            ]);
            session()->flash('info', 'Feuille existante mise à jour.');
        }

        $totalHeures = collect($validated['time_entries'])->sum('heures_reelles');
        $dailyEntry->update(['heures_reelles' => $totalHeures]);
        $dailyEntry->timeEntries()->delete();

        foreach ($validated['time_entries'] as $entry) {
            $dailyEntry->timeEntries()->create([
                'user_id'      => $dailyEntry->user_id,
                'dossier_id'   => $entry['dossier_id'],
                'heure_debut'  => $entry['heure_debut'],
                'heure_fin'    => $entry['heure_fin'],
                'heures_reelles' => $entry['heures_reelles'],
                'travaux'      => $entry['travaux'] ?? null,
                'rendu'        => $entry['rendu'] ?? null,
            ]);
        }

        Alert::success('Succès', 'Feuille de temps enregistrée avec succès.');
        return redirect()->route('daily-entries.show', $dailyEntry)
            ->with('success', 'Feuille de temps enregistrée avec succès.');
    }

    // ------------------------------------------------------------------------
    // SHOW / EDIT / UPDATE / DESTROY
    // ------------------------------------------------------------------------

    public function show(DailyEntry $dailyEntry)
    {
        $this->authorizeView($dailyEntry);
        $dailyEntry->load(['user.poste', 'timeEntries.dossier.client']);
        return view('pages.daily-entries.show', compact('dailyEntry'));
    }

    public function edit(DailyEntry $dailyEntry)
    {
        $this->authorizeEdit($dailyEntry);

        $dailyEntry->load('timeEntries');
        $currentUser = Auth::user();
        $dossiers    = $this->getDossiersForUser($currentUser);
        $clients     = Client::whereIn('statut', ['actif', 'prospect'])->orderBy('nom')->get();

        return view('pages.daily-entries.edit', compact('dailyEntry', 'currentUser', 'dossiers', 'clients'));
    }

    public function update(Request $request, DailyEntry $dailyEntry)
    {
        $this->authorizeEdit($dailyEntry);

        $rules = [
            'user_id'           => 'required|exists:users,id',
            'jour'              => 'required|date',
            'heures_theoriques' => 'required|numeric|min:0|max:24',
            'commentaire'       => 'nullable|string',
            'time_entries'      => 'required|array|min:1',
            'statut'            => 'nullable|in:soumis,validé,refusé',
            'motif_refus'       => 'nullable|string|max:500',
        ];

        if ($request->input('statut') === 'refusé') {
            $rules['motif_refus'] = 'required|string|max:500';
        }

        $validated = $request->validate($rules);

        foreach ($request->time_entries as $index => $entry) {
            $v = Validator::make($entry, [
                'id'             => ['nullable', 'exists:time_entries,id'],
                'dossier_id'     => 'required|exists:dossiers,id',
                'heure_debut'    => 'required|date_format:H:i',
                'heure_fin'      => 'required|date_format:H:i',
                'heures_reelles' => 'required|numeric|min:0.25',
                'travaux'        => 'nullable|string|max:500',
                'rendu'          => 'nullable|string|max:500',
            ]);

            if (
                isset($entry['heure_debut'], $entry['heure_fin'])
                && strtotime($entry['heure_fin']) <= strtotime($entry['heure_debut'])
            ) {
                $v->errors()->add("time_entries.{$index}.heure_fin", 'L\'heure de fin doit être après l\'heure de début.');
            }

            if ($v->fails()) {
                return redirect()->back()->withInput()->withErrors($v->errors());
            }

            if (!empty($entry['id'])) {
                $te = TimeEntry::find($entry['id']);
                if (!$te || $te->daily_entry_id != $dailyEntry->id) {
                    return redirect()->back()->withInput()->withErrors(['error' => 'Activité invalide.']);
                }
            }
        }

        // Vérif doublon si date/user a changé
        $hasChanged = Carbon::parse($dailyEntry->jour)->toDateString() !== Carbon::parse($request->jour)->toDateString()
            || $dailyEntry->user_id != $request->user_id;

        if ($hasChanged) {
            $duplicate = DailyEntry::where('user_id', $request->user_id)
                ->whereDate('jour', $request->jour)
                ->where('id', '!=', $dailyEntry->id)
                ->first();

            if ($duplicate) {
                return redirect()->back()->withInput()
                    ->with('error', 'Une feuille existe déjà pour cet utilisateur et cette date.');
            }
        }

        $totalHeures = collect($request->time_entries)->sum('heures_reelles');
        $jour        = Carbon::parse($request->jour);

        $updateData = [
            'user_id'          => $validated['user_id'],
            'jour'             => $validated['jour'],
            'semaine'          => $jour->isoWeek(),
            'annee_semaine'    => $jour->isoWeekYear(),
            'heures_theoriques' => $validated['heures_theoriques'],
            'heures_reelles'   => $totalHeures,
            'commentaire'      => $validated['commentaire'] ?? null,
            'est_manquant'     => false,
        ];

        // Gestion statut selon rôle
        $authUser = auth()->user();
        if ($request->has('statut') && $this->canValidate($dailyEntry)) {
            $updateData['statut'] = $request->statut;
            if (in_array($request->statut, ['validé', 'refusé'])) {
                $updateData['valide_par'] = auth()->id();
                $updateData['valide_le']  = now();
                if ($request->statut === 'refusé' && $request->has('motif_refus')) {
                    $updateData['motif_refus'] = $request->motif_refus;
                }
            }
        } else {
            $updateData['statut']      = 'soumis';
            $updateData['valide_par']  = null;
            $updateData['valide_le']   = null;
            $updateData['motif_refus'] = null;
        }

        $dailyEntry->update($updateData);

        $existingIds = [];
        foreach ($request->time_entries as $entry) {
            $data = [
                'user_id'        => $dailyEntry->user_id,
                'dossier_id'     => $entry['dossier_id'],
                'heure_debut'    => $entry['heure_debut'],
                'heure_fin'      => $entry['heure_fin'],
                'heures_reelles' => $entry['heures_reelles'],
                'travaux'        => $entry['travaux'] ?? null,
                'rendu'          => $entry['rendu'] ?? null,
            ];

            if (!empty($entry['id'])) {
                $te = $dailyEntry->timeEntries()->find($entry['id']);
                if ($te) {
                    $te->update($data);
                    $existingIds[] = $te->id;
                }
            } else {
                $new = $dailyEntry->timeEntries()->create($data);
                $existingIds[] = $new->id;
            }
        }

        $dailyEntry->timeEntries()->whereNotIn('id', $existingIds)->delete();

        Alert::success('Succès', 'Feuille de temps mise à jour avec succès.');
        return redirect()->route('daily-entries.show', $dailyEntry)
            ->with('success', 'Feuille de temps mise à jour.');
    }

    public function destroy(DailyEntry $dailyEntry)
    {
        $this->authorizeDestroy($dailyEntry);
        $dailyEntry->timeEntries()->delete();
        $dailyEntry->delete();
        Alert::success('Succès', 'Feuille de temps supprimée avec succès.');
        return redirect()->route('daily-entries.index')
            ->with('success', 'Feuille supprimée.');
    }



    // ------------------------------------------------------------------------
    // VALIDATION HEBDOMADAIRE GROUPÉE
    // ------------------------------------------------------------------------

    /**
     * Valider toutes les feuilles soumises d'un collaborateur pour une semaine.
     */
    public function validateWeek(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'semaine' => 'required|integer|min:1|max:53',
            'annee'   => 'required|integer',
        ]);

        $manager = auth()->user();
        $collab  = User::findOrFail($request->user_id);

        // Seul le manager direct peut valider
        if (!$manager->isManagerOf($collab->id) && !$manager->hasRole(['admin', 'super-admin'])) {
            return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas le supérieur de cet utilisateur.'], 403);
        }

        $count = DailyEntry::where('user_id', $request->user_id)
            ->where('semaine', $request->semaine)
            ->where('annee_semaine', $request->annee)
            ->where('statut', 'soumis')
            ->update(['statut' => 'validé', 'valide_par' => $manager->id, 'valide_le' => now()]);

        // Enregistrer la validation hebdomadaire
        WeeklyValidation::updateOrCreate(
            ['user_id' => $request->user_id, 'semaine' => $request->semaine, 'annee' => $request->annee],
            ['validated_by' => $manager->id, 'statut' => 'validé', 'validated_at' => now()]
        );

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) validée(s) pour la semaine {$request->semaine}."]);
    }

    /**
     * Refuser toutes les feuilles d'un collaborateur pour une semaine.
     */
    public function rejectWeek(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'semaine'     => 'required|integer|min:1|max:53',
            'annee'       => 'required|integer',
            'motif_refus' => 'required|string|max:500',
        ]);

        $manager = auth()->user();
        $collab  = User::findOrFail($request->user_id);

        if (!$manager->isManagerOf($collab->id) && !$manager->hasRole(['admin', 'super-admin'])) {
            return response()->json(['success' => false, 'message' => 'Permission refusée.'], 403);
        }

        $count = DailyEntry::where('user_id', $request->user_id)
            ->where('semaine', $request->semaine)
            ->where('annee_semaine', $request->annee)
            ->whereIn('statut', ['soumis', 'refusé'])
            ->update([
                'statut'      => 'refusé',
                'valide_par'  => $manager->id,
                'valide_le'   => now(),
                'motif_refus' => $request->motif_refus,
            ]);

        WeeklyValidation::updateOrCreate(
            ['user_id' => $request->user_id, 'semaine' => $request->semaine, 'annee' => $request->annee],
            ['validated_by' => $manager->id, 'statut' => 'refusé', 'motif_refus' => $request->motif_refus, 'validated_at' => now()]
        );

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) refusée(s)."]);
    }

    // ------------------------------------------------------------------------
    // CRÉATION RAPIDE DE DOSSIER (AJAX)
    // ------------------------------------------------------------------------

    public function createDossierQuick(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'client_id'   => 'nullable|exists:clients,id',
            'type_dossier' => 'nullable|in:audit,conseil,formation,expertise,autre',
            'statut'      => 'nullable|in:ouvert,en_cours,suspendu',
            'description' => 'nullable|string',
        ]);

        $reference = 'DOS-' . strtoupper(substr($validated['nom'], 0, 3)) . '-' . date('Ymd-His');
        // Ajouter le créateur
        if (empty($validated['client_id'])) {
            $clientDefaut = Client::where('nom', 'Coftime')->first();

            if (!$clientDefaut) {
                return back()->withInput()->with('error', 'Client Coftime introuvable en base.');
            }

            $validated['client_id'] = $clientDefaut->id;
        }

        $dossier = Dossier::create([
            'nom'          => $validated['nom'],
            'reference'    => $reference,
            'client_id'    => $validated['client_id'] ?? null,
            'type_dossier' => $validated['type_dossier'] ?? 'autre',
            'statut'       => $validated['statut'] ?? 'ouvert',
            'description'  => $validated['description'] ?? null,
            'created_by'   => auth()->id(),
            'date_ouverture' => now(),
        ]);

        $dossier->load('client');

        return response()->json([
            'success' => true,
            'dossier' => $dossier,
            'client'  => $dossier->client,
        ]);
    }

    // ------------------------------------------------------------------------
    // EXPORT / PDF
    // ------------------------------------------------------------------------

    public function export(Request $request)
    {
        abort_unless(
            auth()->user()->can('exporter les feuilles de temps'),
            403,
            "Vous n'avez pas la permission d'exporter les feuilles de temps."
        );

        $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'format'     => 'required|in:excel,pdf,csv',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin   = Carbon::parse($request->date_fin);
        $format    = $request->format;

        $entries = DailyEntry::with(['user', 'user.poste', 'timeEntries.dossier'])
            ->whereBetween('jour', [$dateDebut, $dateFin])
            ->orderBy('jour', 'desc')
            ->get();

        $filename = 'feuilles-temps_' . $dateDebut->format('Y-m-d') . '_au_' . $dateFin->format('Y-m-d');

        if ($format === 'excel' || $format === 'csv') {
            return Excel::download(
                new DailyEntriesExport($entries, $dateDebut, $dateFin),
                $filename . ($format === 'csv' ? '.csv' : '.xlsx'),
                $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
            );
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pages.daily-entries.export.pdf', compact('entries', 'dateDebut', 'dateFin'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }
    }

    /**
     * Export mensuel (Excel/PDF) des feuilles de temps d'un collaborateur -
     * utilisé par les boutons présents sur chaque ligne de la liste.
     */
    public function exportUserMonth(User $user, string $mois, string $format)
    {
        $moisDate = Carbon::parse($mois . '-01');

        return $this->exportUserPeriodResponse(
            $user,
            $moisDate->copy()->startOfMonth(),
            $moisDate->copy()->endOfMonth(),
            $format,
            $mois
        );
    }

    /**
     * Export (Excel/PDF) des feuilles de temps d'un collaborateur sur une
     * période libre (page "Liste de Temps").
     */
    public function exportUserPeriod(Request $request, User $user)
    {
        // Validation légère : on ne bloque jamais le téléchargement, on retombe
        // sur des valeurs par défaut sensées (mois en cours) si absentes/invalides.
        $debut = $request->filled('date_debut') && strtotime($request->date_debut)
            ? Carbon::parse($request->date_debut)->startOfDay()
            : now()->startOfMonth();

        $fin = $request->filled('date_fin') && strtotime($request->date_fin)
            ? Carbon::parse($request->date_fin)->endOfDay()
            : now()->endOfMonth();

        if ($fin->lt($debut)) {
            [$debut, $fin] = [$fin->copy()->startOfDay(), $debut->copy()->endOfDay()];
        }

        $format = in_array($request->input('format'), ['excel', 'pdf']) ? $request->input('format') : 'pdf';
        $slug   = $debut->format('Y-m-d') . '_au_' . $fin->format('Y-m-d');

        return $this->exportUserPeriodResponse($user, $debut, $fin, $format, $slug);
    }

    /**
     * Génère l'export (Excel/PDF) des feuilles de temps d'un collaborateur
     * pour la période [$debut, $fin]. Réservé à l'administrateur, la
     * direction générale, ou un manager exportant les feuilles d'un de ses
     * subordonnés - et uniquement aux utilisateurs disposant de la
     * permission "exporter les feuilles de temps".
     */
    private function exportUserPeriodResponse(User $user, Carbon $debut, Carbon $fin, string $format, string $slug)
    {
        $authUser = auth()->user();

        abort_unless(
            $authUser->can('exporter les feuilles de temps'),
            403,
            "Vous n'avez pas la permission d'exporter les feuilles de temps."
        );

        $canAccess = $authUser->hasRole(['admin', 'directeur-general'])
            || $authUser->id === $user->id
            || ($authUser->hasRole('manager') && $authUser->isManagerOf($user->id));

        abort_unless($canAccess, 403, 'Vous ne pouvez exporter que les feuilles de vos collaborateurs.');

        $entries = DailyEntry::with(['timeEntries.dossier.client', 'user.poste'])
            ->where('user_id', $user->id)
            ->whereBetween('jour', [$debut, $fin])
            ->orderBy('jour')
            ->get();

        $slugName = \Illuminate\Support\Str::slug($user->prenom . '-' . $user->nom);
        $filename = "feuille-temps_{$slugName}_{$slug}";

        $companySetting = CompanySetting::first();

        if ($format === 'excel') {
            return Excel::download(
                new \App\Exports\UserPeriodTimesheetExport(
                    $user,
                    $entries,
                    $debut,
                    $fin,
                    $this->resolveLogoPath($companySetting)
                ),
                $filename . '.xlsx'
            );
        }

        $logoBase64 = $this->resolveLogoBase64($companySetting);

        $pdf = Pdf::loadView('pages.daily-entries.export.user-period-pdf', [
            'user'           => $user->load('poste'),
            'entries'        => $entries,
            'debut'          => $debut,
            'fin'            => $fin,
            'logoBase64'     => $logoBase64,
            'companySetting' => $companySetting,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Page "Liste de Temps" - sélection d'un collaborateur et d'une période
     * libre, avec aperçu et export Excel/PDF. Réservée à l'administrateur,
     * la direction générale, ou un manager (pour ses subordonnés).
     */
    public function listByUser(Request $request)
    {
        $authUser = auth()->user();

        if ($authUser->hasRole(['admin', 'directeur-general'])) {
            $users = User::where('is_active', 1)->orderBy('prenom')->get(['id', 'prenom', 'nom']);
        } elseif ($authUser->hasRole('manager')) {
            $users = User::where('is_active', 1)
                ->where(fn($q) => $q->where('id', $authUser->id)->orWhere('manager_id', $authUser->id))
                ->orderBy('prenom')
                ->get(['id', 'prenom', 'nom']);
        } else {
            $users = User::where('id', $authUser->id)->get(['id', 'prenom', 'nom']);
        }

        $selectedUser = null;
        $entries      = collect();
        $dateDebut    = null;
        $dateFin      = null;

        // On ne bloque jamais l'affichage par une validation stricte : seul le
        // choix d'un collaborateur est nécessaire, la période retombe sur le
        // mois en cours si elle est absente ou invalide.
        if ($request->filled('user_id') && User::where('id', $request->input('user_id'))->exists()) {
            $requestedId = (int) $request->input('user_id');
            $canAccess   = $authUser->hasRole(['admin', 'directeur-general'])
                || $authUser->id === $requestedId
                || ($authUser->hasRole('manager') && $authUser->isManagerOf($requestedId));

            abort_unless($canAccess, 403, "Vous n'avez pas accès aux feuilles de temps de ce collaborateur.");

            $selectedUser = User::findOrFail($requestedId);

            $dateDebut = $request->filled('date_debut') && strtotime($request->input('date_debut'))
                ? Carbon::parse($request->input('date_debut'))->startOfDay()
                : now()->startOfMonth();

            $dateFin = $request->filled('date_fin') && strtotime($request->input('date_fin'))
                ? Carbon::parse($request->input('date_fin'))->endOfDay()
                : now()->endOfMonth();

            if ($dateFin->lt($dateDebut)) {
                [$dateDebut, $dateFin] = [$dateFin->copy()->startOfDay(), $dateDebut->copy()->endOfDay()];
            }

            $entries = DailyEntry::with(['timeEntries.dossier.client', 'user.poste'])
                ->where('user_id', $requestedId)
                ->whereBetween('jour', [$dateDebut, $dateFin])
                ->orderBy('jour')
                ->get();
        }

        return view('pages.daily-entries.liste', compact(
            'users', 'selectedUser', 'entries', 'dateDebut', 'dateFin'
        ));
    }

    public function pdf(DailyEntry $dailyEntry)
    {
        $dailyEntry->load(['user.poste', 'timeEntries.dossier']);

        if (!$dailyEntry->exists || is_null($dailyEntry->jour)) {
            abort(404, 'Feuille introuvable.');
        }

        $companySetting = CompanySetting::first();
        $logoBase64     = $this->resolveLogoBase64($companySetting);
        $dateFile       = Carbon::parse($dailyEntry->jour)->format('Y-m-d');

        $pdf = Pdf::loadView('pages.daily-entries.export.pdf1', [
            'entry'          => $dailyEntry,
            'logoBase64'     => $logoBase64,
            'companySetting' => $companySetting,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("feuille-temps-{$dateFile}.pdf");
    }

    // ------------------------------------------------------------------------
    // HELPERS PRIVÉS
    // ------------------------------------------------------------------------

    private function resolveSemaine(Request $request): array
    {
        if ($request->filled('semaine') && $request->filled('annee')) {
            return [(int) $request->semaine, (int) $request->annee];
        }
        return [now()->isoWeek(), now()->isoWeekYear()];
    }

    /**
     * Retourne le logo de l'entreprise encodé en base64 pour les PDF, en
     * suivant la même chaîne de résolution que CompanySetting::logo_url :
     * logo uploadé (storage/app/public) puis logo par défaut du thème.
     */
    private function resolveLogoBase64(?CompanySetting $companySetting): ?string
    {
        $path = $this->resolveLogoPath($companySetting);
        if (!$path) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return "data:{$mime};base64," . base64_encode(file_get_contents($path));
    }

    /**
     * Chemin disque du logo de l'entreprise, en suivant la même chaîne de
     * résolution que CompanySetting::logo_url : logo uploadé
     * (storage/app/public) puis logo par défaut du thème.
     */
    private function resolveLogoPath(?CompanySetting $companySetting): ?string
    {
        $candidates = [];

        if ($companySetting?->logo) {
            $candidates[] = storage_path('app/public/' . $companySetting->logo);
        }

        $candidates[] = public_path('assets/img/logo_cofima_bon.jpg');
        $candidates[] = public_path('assets/img/logo-seul-cofima.png');

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function getDossiersForUser(User $user)
    {
        if ($user->hasRole(['admin', 'super-admin', 'manager', 'directeur-general'])) {
            return Dossier::with('client')->whereIn('statut', ['ouvert', 'en_cours'])->orderBy('nom')->get();
        }

        return Dossier::with(['client', 'collaborateurs'])
            ->whereIn('statut', ['ouvert', 'en_cours'])
            ->where(
                fn($q) =>
                $q->where('created_by', $user->id)
                    ->orWhereHas(
                        'collaborateurs',
                        fn($s) =>
                        $s->where('users.id', $user->id)->where('collaborateur_dossier.is_active', 1)
                    )
            )
            ->orderBy('nom')
            ->get();
    }

    private function authorizeEdit(DailyEntry $entry): void
    {
        $user = auth()->user();

        if ($user->hasRole('directeur-general')) return;

        // Propriétaire uniquement (le manager ne modifie pas la feuille de quelqu'un d'autre)
        if ($entry->user_id !== $user->id) {
            abort(403, 'Seul le propriétaire peut modifier sa feuille de temps.');
        }
    }

    private function canValidate(DailyEntry $entry): bool
    {
        $user = auth()->user();

        // Ne peut pas valider sa propre feuille
        if ($entry->user_id === $user->id) return false;

        if ($user->hasRole('directeur-general')) return true;

        return $user->hasRole('manager') && $user->isManagerOf($entry->user_id);
    }

    private function authorizeView(DailyEntry $entry): void
    {
        $user = auth()->user();

        if ($user->hasRole('directeur-general')) return;

        $canSee = $entry->user_id === $user->id
            || ($user->hasRole('manager') && $user->isManagerOf($entry->user_id));

        if (!$canSee) {
            abort(403, 'Vous n\'avez pas accès à cette feuille de temps.');
        }
    }

    private function authorizeDestroy(DailyEntry $entry): void
    {
        $user = auth()->user();

        if ($user->hasRole('directeur-general')) return;

        $canDelete = $entry->user_id === $user->id
            || ($user->hasRole('manager') && $user->isManagerOf($entry->user_id));

        if (!$canDelete) {
            abort(403, 'Vous n\'avez pas le droit de supprimer cette feuille.');
        }
    }

    private function ensureCanManage(DailyEntry $entry): void
    {
        if (!$this->canValidate($entry)) {
            abort(403, 'Vous n\'êtes pas le supérieur hiérarchique de cet utilisateur.');
        }
    }

    /**
     * Valider une feuille de temps (pour les responsables)
     */
    public function validateEntry(DailyEntry $dailyEntry)
    {
        $dailyEntry->update([
            'statut' => 'validé',
            'valide_par' => Auth::id(),
            'valide_le' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Feuille de temps validée avec succès.');
    }

    /**
     * Refuser une feuille de temps (pour les responsables)
     */
    public function rejectEntry(DailyEntry $dailyEntry, Request $request)
    {
        // Autoriser le refus même si déjà refusé (pour changer le motif)
        // ou bloquer si déjà validé
        if ($dailyEntry->statut === 'validé') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de refuser une feuille déjà validée.'
            ], 422);
        }

        $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $dailyEntry->update([
            'statut'      => 'refusé',
            'valide_par'  => Auth::id(),
            'valide_le'   => now(),
            'motif_refus' => $request->motif_refus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feuille de temps refusée avec succès.'
        ]);
    }


    public function bulkValidate(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:daily_entries,id']);

        $count = DailyEntry::whereIn('id', $request->ids)
            ->where('user_id', '!=', auth()->id())
            ->whereIn('statut', ['soumis'])
            ->update(['statut' => 'validé', 'valide_par' => auth()->id(), 'valide_le' => now()]);

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) validée(s)."]);
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids'         => 'required|array',
            'ids.*'       => 'exists:daily_entries,id',
            'motif_refus' => 'required|string|max:500',
        ]);

        $count = DailyEntry::whereIn('id', $request->ids)
            ->where('user_id', '!=', auth()->id())
            ->whereIn('statut', ['soumis', 'refusé'])
            ->update(['statut' => 'refusé', 'valide_par' => auth()->id(), 'valide_le' => now(), 'motif_refus' => $request->motif_refus]);

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) refusée(s)."]);
    }

    public function validateAll(Request $request)
    {
        $request->validate(['mois' => 'required|date_format:Y-m']);

        $mois  = Carbon::parse($request->mois . '-01');
        $count = DailyEntry::where('statut', 'soumis')
            ->where('user_id', '!=', auth()->id())
            ->whereBetween('jour', [$mois->copy()->startOfMonth(), $mois->copy()->endOfMonth()])
            ->update(['statut' => 'validé', 'valide_par' => auth()->id(), 'valide_le' => now()]);

        return response()->json(['success' => true, 'message' => "{$count} feuille(s) validée(s) pour {$request->mois}."]);
    }

}
