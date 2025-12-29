<?php

namespace App\Http\Controllers;

use App\Models\CadeauInvitation;
use App\Models\CompanySetting;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert; // AJOUTÉ
use Barryvdh\DomPDF\Facade\Pdf;


class CadeauInvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Lecture (Liste, Détails, PDF, Téléchargement doc)
        $this->middleware('permission:voir les cadeaux et invitations')
            ->only(['index', 'show', 'downloadDocument', 'generatePdf']);

        // Création
        $this->middleware('permission:créer des cadeaux et invitations')->only(['create', 'store']);

        // Modification
        $this->middleware('permission:modifier des cadeaux et invitations')->only(['edit', 'update']);

        // Suppression
        $this->middleware('permission:supprimer des cadeaux et invitations')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cadeaux = CadeauInvitation::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.cadeaux.index', compact('cadeaux'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('is_active', true)->get();
        return view('pages.cadeaux.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'date' => 'required|date',
                'cadeau_hospitalite' => 'required|string|max:255',
                'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'description' => 'nullable|string',
                'valeurs' => 'nullable|numeric|min:0',
                'action_prise' => 'required|in:accepté,refusé,en_attente',
                'responsable_id' => 'required|exists:users,id'
            ]);

            // Upload du fichier
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('cadeau-invitations/documents', $fileName, 'public');
                $validated['document'] = $filePath;
            } else {
                $validated['document'] = null;    // ✔ Évite undefined array key
            }

            // Ajout automatique de l'utilisateur connecté
            $validated['user_id'] = auth()->id();

            // Enregistrement
            CadeauInvitation::create($validated);

            Alert::success('Succès', 'Cadeau/Invitation créé avec succès.');
            return redirect()->route('cadeau-invitations.index');
        } catch (\Exception $e) {

            // Logs Laravel pour debug (facultatif mais conseillé)
            Log::error('Erreur Création CadeauInvitation : ' . $e->getMessage());

            Alert::error('Erreur', 'Une erreur est survenue lors de l’enregistrement.');
            return back()->withInput()->withErrors([
                'error' => $e->getMessage()  // Pour comprendre rapidement ce qui bloque
            ]);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(CadeauInvitation $cadeauInvitation)
    {
        return view('pages.cadeaux.show', compact('cadeauInvitation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CadeauInvitation $cadeauInvitation)
    {
        $users = User::where('is_active', true)->get();
        return view('pages.cadeaux.edit', compact('cadeauInvitation', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CadeauInvitation $cadeauInvitation)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'date' => 'required|date',
                'cadeau_hospitalite' => 'required|string|max:255',
                'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'description' => 'nullable|string',
                'valeurs' => 'nullable|numeric|min:0',
                'action_prise' => 'required|in:accepté,refusé,en_attente',
                'responsable_id' => 'required|exists:users,id'
            ]);

            // Gestion du fichier
            if ($request->hasFile('document')) {

                // Suppression de l'ancien fichier
                if ($cadeauInvitation->document && Storage::disk('public')->exists($cadeauInvitation->document)) {
                    Storage::disk('public')->delete($cadeauInvitation->document);
                }

                // Upload du nouveau fichier
                $file = $request->file('document');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('cadeau-invitations/documents', $fileName, 'public');
                $validated['document'] = $filePath;
            } else {
                // Garder l’ancien fichier
                $validated['document'] = $cadeauInvitation->document;
            }

            // Mettre à jour l'utilisateur qui modifie (optionnel)
            $validated['user_id'] = auth()->id();

            // Mise à jour en base
            $cadeauInvitation->update($validated);

            Alert::success('Mis à jour', 'Cadeau / Invitation modifié avec succès.');
            return redirect()->route('cadeau-invitations.index');
        } catch (\Exception $e) {

            Log::error("Erreur update CadeauInvitation : " . $e->getMessage());

            Alert::error('Erreur', 'Une erreur est survenue lors de la mise à jour.');
            return back()->withInput()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CadeauInvitation $cadeauInvitation)
    {
        if ($cadeauInvitation->document && Storage::disk('public')->exists($cadeauInvitation->document)) {
            Storage::disk('public')->delete($cadeauInvitation->document);
        }


        Alert::success('Supprimé', 'Cadeau / Invitation supprimé avec succès.');

        return redirect()->route('cadeau-invitations.index');
    }

    /**
     * Télécharger le document
     */
    public function downloadDocument(CadeauInvitation $cadeauInvitation)
    {
        if (!$cadeauInvitation->document || !Storage::disk('public')->exists($cadeauInvitation->document)) {
            Alert::error('Erreur', 'Le document demandé est introuvable.');
            return back();
        }

        $path = Storage::disk('public')->path($cadeauInvitation->document);
        return response()->download($path, basename($path));
    }

    /**

     * Génère la fiche Cadeau/Invitation au format PDF.

     * @param CadeauInvitation $cadeauInvitation

     * @return \Illuminate\Http\Response

     */

    public function generatePdf(CadeauInvitation $cadeauInvitation)

    {
        // 1. Fonction d'aide locale pour l'encodage Base64
        $getAssetBase64 = function ($path) {

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

                return null;
            }

            return null;
        };

        // 2. Récupération des données globales

        $companySetting = CompanySetting::first();

        $logoBase64 = $companySetting && $companySetting->logo

            ? $getAssetBase64($companySetting->logo)

            : null;

        // 3. Définition de la vue et passage des données

        $pdf = Pdf::loadView('pdf.cadeau', [

            'cadeauInvitation' => $cadeauInvitation,

            'companySetting' => $companySetting,

            'logoBase64' => $logoBase64,

        ]);

        // 4. Affichage du PDF dans le navigateur

        $filename = 'Cadeau_Invitation_N' . $cadeauInvitation->id . '.pdf';
        return $pdf->stream($filename);
    }
}
