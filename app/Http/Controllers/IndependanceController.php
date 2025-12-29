<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Independance;
use App\Models\LogActivite;
use App\Models\User;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log; // AJOUTÉ
use Barryvdh\DomPDF\Facade\Pdf; // Assurez-vous d'avoir installé cette librairie
use Illuminate\Support\Facades\Storage;



class IndependanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Lecture (Liste, Détails et PDF)
        $this->middleware('permission:voir les indépendances')->only(['index', 'show', 'generatePdf']);

        // Création
        $this->middleware('permission:créer des indépendances')->only(['create', 'store']);

        // Modification
        $this->middleware('permission:modifier des indépendances')->only(['edit', 'update']);

        // Suppression
        $this->middleware('permission:supprimer des indépendances')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info('Accès à la liste des déclarations d\'indépendance', [
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email ?? 'unknown'
        ]);

        // CORRECTION : Charger les indépendances avec les relations nécessaires
        $independances = Independance::with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // CORRECTION : Passer la variable $independances à la vue
        return view('pages.independance.index', compact('independances'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Log::info('Accès au formulaire de création de déclaration d\'indépendance', [
            'user_id' => auth()->id()
        ]);



        $users = User::where('is_active', true)->get();
        return view('pages.independance.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Tentative de création d\'une déclaration d\'indépendance', [
            'user_id' => auth()->id(),
            'data' => $request->except(['_token'])
        ]);

        $validated = $request->validate([
            'nom_client' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'siege_social' => 'nullable|string|max:255',
            'type_entite' => 'nullable|string|max:255',
            'frais_audit' => 'nullable|numeric|min:0',
            'frais_non_audit' => 'nullable|numeric|min:0',
            'honoraire_audit_exercice' => 'nullable|integer|min:0',
            'honoraire_audit_travail' => 'nullable|integer|min:0',
            'associes_mission' => 'nullable|array',
            'associes_mission.*' => 'exists:users,id',
            'nombres_annees_experiences' => 'nullable|string',
            'question_independance' => 'nullable|string',
            'actions_recquise' => 'nullable|string',
            'autres_services_fournit' => 'nullable|string',
            'responsable_audit' => 'nullable|array',
            'responsable_audit.*' => 'exists:users,id',
        ]);

        try {
            // Ajouter l'utilisateur connecté automatiquement
            $validated['user_id'] = auth()->id();

            // Calcul du total des frais
            $validated['total_frais'] = ($validated['frais_audit'] ?? 0) + ($validated['frais_non_audit'] ?? 0);

            // Conversion des arrays en strings
            if (isset($validated['associes_mission'])) {
                $validated['associes_mission'] = implode(',', $validated['associes_mission']);
            }
            if (isset($validated['responsable_audit'])) {
                $validated['responsable_audit'] = implode(',', $validated['responsable_audit']);
            }

            $independance = Independance::create($validated);

            Log::info('Déclaration d\'indépendance créée avec succès', [
                'user_id' => auth()->id(),
                'independance_id' => $independance->id,
                'nom_client' => $independance->nom_client
            ]);

            Alert::success('Succès', "Déclaration d'indépendance créée avec succès.");

            return redirect()->route('independances.index');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la déclaration d\'indépendance', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            Alert::error('Erreur', "Une erreur est survenue lors de la création : " . $e->getMessage());

            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Independance $independance)
    {
        Log::info('Consultation d\'une déclaration d\'indépendance', [
            'user_id' => auth()->id(),
            'independance_id' => $independance->id
        ]);


        return view('pages.independance.show', compact('independance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Independance $independance)
    {
        Log::info('Accès au formulaire d\'édition d\'une déclaration d\'indépendance', [
            'user_id' => auth()->id(),
            'independance_id' => $independance->id
        ]);
        $independances = Independance::with(['user'])->orderBy('created_at', 'desc')
            ->get();
        $users = User::where('is_active', true)->get();
        return view('pages.independance.edit', compact('independance', 'users', 'independances'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Independance $independance)
    {
        Log::info('Tentative de modification d\'une déclaration d\'indépendance', [
            'user_id' => auth()->id(),
            'independance_id' => $independance->id,
            'data' => $request->except(['_token', '_method'])
        ]);

        $validated = $request->validate([
            'nom_client' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'siege_social' => 'nullable|string|max:255',
            'type_entite' => 'nullable|string|max:255',
            'frais_audit' => 'nullable|numeric|min:0',
            'frais_non_audit' => 'nullable|numeric|min:0',
            'honoraire_audit_exercice' => 'nullable|integer|min:0',
            'honoraire_audit_travail' => 'nullable|integer|min:0',
            'associes_mission' => 'nullable|array',
            'associes_mission.*' => 'exists:users,id',
            'nombres_annees_experiences' => 'nullable|string',
            'question_independance' => 'nullable|string',
            'actions_recquise' => 'nullable|string',
            'autres_services_fournit' => 'nullable|string',
            'responsable_audit' => 'nullable|array',
            'responsable_audit.*' => 'exists:users,id',
        ]);

        try {
            $validated['user_id'] = auth()->id();

            // Calcul du total des frais
            $validated['total_frais'] = ($validated['frais_audit'] ?? 0) + ($validated['frais_non_audit'] ?? 0);

            // Conversion des arrays en strings
            if (isset($validated['associes_mission'])) {
                $validated['associes_mission'] = implode(',', $validated['associes_mission']);
            }
            if (isset($validated['responsable_audit'])) {
                $validated['responsable_audit'] = implode(',', $validated['responsable_audit']);
            }

            $independance->update($validated);

            Log::info('Déclaration d\'indépendance modifiée avec succès', [
                'user_id' => auth()->id(),
                'independance_id' => $independance->id,
                'nom_client' => $independance->nom_client
            ]);

            Alert::success('Succès', "Déclaration d'indépendance modifiée avec succès.");

            return redirect()->route('independances.index');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification de la déclaration d\'indépendance', [
                'user_id' => auth()->id(),
                'independance_id' => $independance->id,
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            Alert::error('Erreur', "Une erreur est survenue lors de la modification : " . $e->getMessage());

            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Independance $independance)
    {
        Log::info('Tentative de suppression d\'une déclaration d\'indépendance', [
            'user_id' => auth()->id(),
            'independance_id' => $independance->id,
            'nom_client' => $independance->nom_client
        ]);

        try {
            $nomClient = $independance->nom_client;
            $independance->delete();

            Log::info('Déclaration d\'indépendance supprimée avec succès', [
                'user_id' => auth()->id(),
                'nom_client' => $nomClient
            ]);

            Alert::success('Succès', "Déclaration d'indépendance supprimée avec succès.");
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la déclaration d\'indépendance', [
                'user_id' => auth()->id(),
                'independance_id' => $independance->id,
                'error' => $e->getMessage()
            ]);

            Alert::error('Erreur', "Une erreur est survenue lors de la suppression : " . $e->getMessage());
        }

        return redirect()->route('independances.index');
    }

    public function generatePdf(Independance $independance)

    {

        // 1. Fonction d'aide locale pour l'encodage Base64

        $getLogoBase64 = function ($path) {

            if (!$path) {
                return null;
            }



            try {

                $fullPath = Storage::disk('public')->path($path);



                if (file_exists($fullPath)) {

                    $imageData = file_get_contents($fullPath);

                    $mimeType = mime_content_type($fullPath);

                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            } catch (\Exception $e) {

                // En cas d'erreur de lecture

                return null;
            }

            return null;
        };



        // 2. Récupération des données globales

        $companySetting = CompanySetting::first();

        $logoBase64 = $companySetting && $companySetting->logo

            ? $getLogoBase64($companySetting->logo)

            : null;



        // 3. Préparation des données spécifiques (l'objet $independance est déjà chargé)

        // Note: Nous n'avons pas besoin de signatures d'assignation ici, car

        // les informations de validation sont intégrées dans les données d'audit elles-mêmes.



        // 4. Définition de la vue et passage des données

        $pdf = Pdf::loadView('pdf.independance_pdf', [

            'independance' => $independance,

            'companySetting' => $companySetting,

            'logoBase64' => $logoBase64,

        ]);



        // 5. Affichage ou téléchargement du PDF

        $filename = 'Declaration_Independance_' . $independance->nom_client . '.pdf';



        return $pdf->stream($filename);

        // Ou return $pdf->download($filename); pour télécharger directement

    }
}
