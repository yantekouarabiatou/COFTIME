<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DossierController extends Controller
{
    public function index(Request $request)
    {
        $query = Dossier::with('client')->latest();

        // Recherche
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filtres
        if ($request->has('statut')) {
            if ($request->statut == 'en_retard') {
                $query->enRetard();
            } else {
                $query->where('statut', $request->statut);
            }
        }

        if ($request->has('type')) {
            $query->parType($request->type);
        }

        $dossiers = $query->paginate(20);

        // Statistiques pour la vue
        $totalDossiers = Dossier::count();
        $dossiersEnCours = Dossier::enCours()->count();
        $dossiersEnRetard = Dossier::enRetard()->count();
        $dossiersClotures = Dossier::cloture()->count();

        return view('pages.dossiers.index', compact(
            'dossiers',
            'totalDossiers',
            'dossiersEnCours',
            'dossiersEnRetard',
            'dossiersClotures'
        ));
    }

    public function create()
    {
        $clients = Client::whereIn('statut', ['actif', 'prospect'])->get();
        return view('pages.dossiers.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'nom' => 'required|string|max:255',
            'reference' => 'nullable|string|max:50|unique:dossiers,reference',
            'type_dossier' => 'required|in:audit,conseil,formation,expertise,autre',
            'statut' => 'required|in:ouvert,en_cours,suspendu,cloture,archive',
            'description' => 'nullable|string',
            'date_ouverture' => 'required|date',
            'date_cloture_prevue' => 'nullable|date|after_or_equal:date_ouverture',
            'date_cloture_reelle' => 'nullable|date|after_or_equal:date_ouverture',
            'budget' => 'nullable|numeric|min:0',
            'frais_dossier' => 'nullable|numeric|min:0',
            'document' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'notes' => 'nullable|string',
        ]);

        // Gestion du document
        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')->store('dossiers/documents', 'public');
        }

        $dossier = Dossier::create($validated);

        return redirect()->route('dossiers.show', $dossier)
            ->with('success', 'Dossier créé avec succès.');
    }

    public function show(Dossier $dossier)
    {
        return view('pages.dossiers.show', compact('dossier'));
    }

    public function edit(Dossier $dossier)
    {
        $clients = Client::whereIn('statut', ['actif', 'prospect'])->get();
        return view('pages.dossiers.edit', compact('dossier', 'clients'));
    }

    public function update(Request $request, Dossier $dossier)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'nom' => 'required|string|max:255',
            'reference' => 'required|string|max:50|unique:dossiers,reference,' . $dossier->id,
            'type_dossier' => 'required|in:audit,conseil,formation,expertise,autre',
            'statut' => 'required|in:ouvert,en_cours,suspendu,cloture,archive',
            'description' => 'nullable|string',
            'date_ouverture' => 'required|date',
            'date_cloture_prevue' => 'nullable|date|after_or_equal:date_ouverture',
            'date_cloture_reelle' => 'nullable|date|after_or_equal:date_ouverture',
            'budget' => 'nullable|numeric|min:0',
            'frais_dossier' => 'nullable|numeric|min:0',
            'document' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'notes' => 'nullable|string',
        ]);

        // Gestion de la suppression du document
        if ($request->has('remove_document') && $dossier->document) {
            Storage::disk('public')->delete($dossier->document);
            $validated['document'] = null;
        }

        // Gestion du nouveau document
        if ($request->hasFile('document')) {
            // Supprimer l'ancien document s'il existe
            if ($dossier->document) {
                Storage::disk('public')->delete($dossier->document);
            }
            $validated['document'] = $request->file('document')->store('dossiers/documents', 'public');
        } else {
            // Conserver le document existant si aucun nouveau n'est fourni
            $validated['document'] = $dossier->document;
        }

        $dossier->update($validated);

        return redirect()->route('dossiers.show', $dossier)
            ->with('success', 'Dossier mis à jour avec succès.');
    }

    public function destroy(Dossier $dossier)
    {
        // Supprimer le document associé s'il existe
        if ($dossier->document) {
            Storage::disk('public')->delete($dossier->document);
        }

        $dossier->delete();

        return redirect()->route('dossiers.index')
            ->with('success', 'Dossier supprimé avec succès.');
    }
}
