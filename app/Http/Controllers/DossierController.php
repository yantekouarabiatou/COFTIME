<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DossierController extends Controller
{
    /**
     * Afficher la liste des dossiers
     */
    public function index(Request $request)
    {
        $query = Dossier::with('client')->latest();

        // Filtre par client si spécifié
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        $dossiers = $query->get();
        $clients = Client::orderBy('nom')->get();

        return view('pages.dossiers.index', compact('dossiers', 'clients'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(Request $request)
    {
        $clients = Client::orderBy('nom')->get();
        $selectedClient = $request->client_id ? Client::find($request->client_id) : null;

        return view('pages.dossiers.create', compact('clients', 'selectedClient'));
    }

    /**
     * Enregistrer un nouveau dossier
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'nom' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100|unique:dossiers,reference',
            'type_dossier' => 'required|in:audit,conseil,formation,expertise,autre',
            'description' => 'nullable|string',
            'date_ouverture' => 'required|date',
            'date_cloture_prevue' => 'nullable|date|after_or_equal:date_ouverture',
            'statut' => 'required|in:ouvert,en_cours,suspendu,cloture,archive',
            'budget' => 'nullable|numeric|min:0',
            'frais_dossier' => 'nullable|numeric|min:0',
            'document' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
        }

        // Traitement du document
        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('dossiers/documents', 'public');
        }

        // Création du dossier
        $dossier = Dossier::create([
            'client_id' => $request->client_id,
            'nom' => $request->nom,
            'reference' => $request->reference ?? $this->generateReference(),
            'type_dossier' => $request->type_dossier,
            'description' => $request->description,
            'date_ouverture' => $request->date_ouverture,
            'date_cloture_prevue' => $request->date_cloture_prevue,
            'date_cloture_reelle' => null,
            'statut' => $request->statut,
            'budget' => $request->budget,
            'frais_dossier' => $request->frais_dossier,
            'document' => $documentPath,
            'notes' => $request->notes,
        ]);

        return redirect()->route('dossiers.index')
            ->with('success', 'Dossier créé avec succès!');
    }

    /**
     * Afficher les détails d'un dossier
     */
    public function show(Dossier $dossier)
    {
        $dossier->load('client');
        return view('pages.dossiers.show', compact('dossier'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Dossier $dossier)
    {
        $clients = Client::orderBy('nom')->get();
        return view('pages.dossiers.edit', compact('dossier', 'clients'));
    }

    /**
     * Mettre à jour un dossier
     */
    public function update(Request $request, Dossier $dossier)
    {
        // Validation avec règles uniques excluant le dossier actuel
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'nom' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100|unique:dossiers,reference,' . $dossier->id,
            'type_dossier' => 'required|in:audit,conseil,formation,expertise,autre',
            'description' => 'nullable|string',
            'date_ouverture' => 'required|date',
            'date_cloture_prevue' => 'nullable|date|after_or_equal:date_ouverture',
            'statut' => 'required|in:ouvert,en_cours,suspendu,cloture,archive',
            'budget' => 'nullable|numeric|min:0',
            'frais_dossier' => 'nullable|numeric|min:0',
            'document' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
        }

        // Traitement du document
        if ($request->hasFile('document')) {
            // Supprimer l'ancien document s'il existe
            if ($dossier->document && Storage::disk('public')->exists($dossier->document)) {
                Storage::disk('public')->delete($dossier->document);
            }

            $documentPath = $request->file('document')->store('dossiers/documents', 'public');
            $dossier->document = $documentPath;
        }

        // Mise à jour automatique de la date de clôture si statut "cloture"
        $dateClotureReelle = $dossier->date_cloture_reelle;
        if ($request->statut == 'cloture' && $dossier->statut != 'cloture') {
            $dateClotureReelle = now()->format('Y-m-d');
        } elseif ($request->statut != 'cloture') {
            $dateClotureReelle = null;
        }

        // Mise à jour du dossier
        $dossier->update([
            'client_id' => $request->client_id,
            'nom' => $request->nom,
            'reference' => $request->reference ?? $dossier->reference,
            'type_dossier' => $request->type_dossier,
            'description' => $request->description,
            'date_ouverture' => $request->date_ouverture,
            'date_cloture_prevue' => $request->date_cloture_prevue,
            'date_cloture_reelle' => $dateClotureReelle,
            'statut' => $request->statut,
            'budget' => $request->budget,
            'frais_dossier' => $request->frais_dossier,
            'notes' => $request->notes,
        ]);

        return redirect()->route('dossiers.index')
            ->with('success', 'Dossier mis à jour avec succès!');
    }

    /**
     * Supprimer un dossier
     */
    public function destroy(Dossier $dossier)
    {
        // Supprimer le document s'il existe
        if ($dossier->document && Storage::disk('public')->exists($dossier->document)) {
            Storage::disk('public')->delete($dossier->document);
        }

        $dossier->delete();

        return redirect()->route('dossiers.index')
            ->with('success', 'Dossier supprimé avec succès!');
    }

    /**
     * Télécharger le document
     */
    public function downloadDocument(Dossier $dossier)
    {
        if (!$dossier->document || !Storage::disk('public')->exists($dossier->document)) {
            return redirect()->back()
                ->with('error', 'Document non trouvé.');
        }

        return Storage::disk('public')->download($dossier->document,
            'dossier-' . str_slug($dossier->nom) . '.' . pathinfo($dossier->document, PATHINFO_EXTENSION));
    }

    /**
     * Générer une référence automatique
     */
    private function generateReference()
    {
        $year = date('Y');
        $month = date('m');
        $lastDossier = Dossier::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastDossier ? (int) substr($lastDossier->reference, -4) + 1 : 1;

        return 'DOS-' . $year . $month . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Changer le statut d'un dossier
     */
    public function changeStatus(Request $request, Dossier $dossier)
    {
        $request->validate([
            'statut' => 'required|in:ouvert,en_cours,suspendu,cloture,archive'
        ]);

        $oldStatus = $dossier->statut;
        $newStatus = $request->statut;

        $dossier->statut = $newStatus;

        // Mettre à jour la date de clôture réelle si nécessaire
        if ($newStatus == 'cloture' && $oldStatus != 'cloture') {
            $dossier->date_cloture_reelle = now()->format('Y-m-d');
        } elseif ($newStatus != 'cloture') {
            $dossier->date_cloture_reelle = null;
        }

        $dossier->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'statut' => $dossier->statut,
            'statut_badge' => $dossier->statut_badge
        ]);
    }
}
