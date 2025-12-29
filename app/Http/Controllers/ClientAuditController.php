<?php

namespace App\Http\Controllers;

use App\Models\ClientAudit;
use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert; // ← AJOUTÉ
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\CompanySettingSeeder;

class ClientAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Lecture (Liste, Détails, Téléchargement PDF et Document joint)
        $this->middleware('permission:voir les clients audit')->only(['index', 'show', 'downloadDocument', 'generatePdf']);

        // Création
        $this->middleware('permission:créer des clients audit')->only(['create', 'store']);

        // Modification
        $this->middleware('permission:modifier des clients audit')->only(['edit', 'update']);

        // Suppression
        $this->middleware('permission:supprimer des clients audit')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = ClientAudit::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('is_active', true)->get();
        return view('pages.clients.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_client' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'siege_social' => 'nullable|string|max:255',
            'frais_audit' => 'nullable|numeric|min:0',
            'frais_autres' => 'nullable|numeric|min:0',
        ]);

        // Gestion du fichier document
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('clients-audit/documents', $fileName, 'public');
            $validated['document'] = $filePath;
        }

        ClientAudit::create([
            'nom_client' => $validated['nom_client'],
            'adresse' => $validated['adresse'] ?? null,
            'document' => $validated['document'] ?? null, // ✔ FIX
            'siege_social' => $validated['siege_social'] ?? null,
            'frais_audit' => $validated['frais_audit'] ?? null,
            'frais_autres' => $validated['frais_autres'] ?? null,
            'user_id' => auth()->id(),
        ]);

        Alert::success('Succès', 'Client audit créé avec succès.');

        return redirect()->route('clients-audit.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(ClientAudit $clientAudit)
    {
        $clientAudit->load('user');
        return view('pages.clients.show', compact('clientAudit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClientAudit $clientAudit)
    {
        $users = User::where('is_active', true)->get();
        return view('pages.clients.edit', compact('clientAudit', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClientAudit $clientAudit)
    {
        $validated = $request->validate([
            'nom_client' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'siege_social' => 'nullable|string|max:255',
            'frais_audit' => 'nullable|numeric|min:0',
            'frais_autres' => 'nullable|numeric|min:0',
        ]);

        // Gestion du fichier document
        if ($request->hasFile('document')) {
            if ($clientAudit->document && Storage::disk('public')->exists($clientAudit->document)) {
                Storage::disk('public')->delete($clientAudit->document);
            }

            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('clients-audit/documents', $fileName, 'public');
            $validated['document'] = $filePath;
        } else {
            $validated['document'] = $clientAudit->document;
        }

        $clientAudit->update($validated);

        Alert::success('Mis à jour', 'Client audit modifié avec succès.'); // ← Alert SweetAlert

        return redirect()->route('clients-audit.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientAudit $clientAudit)
    {
        if ($clientAudit->document && Storage::disk('public')->exists($clientAudit->document)) {
            Storage::disk('public')->delete($clientAudit->document);
        }

        $clientAudit->delete();

        Alert::success('Supprimé', 'Client audit supprimé avec succès.'); // ← Alert SweetAlert

        return redirect()->route('clients-audit.index');
    }

    /**
     * Télécharger le document
     */
    public function downloadDocument(ClientAudit $clientAudit)
    {
        if (!$clientAudit->document || !Storage::disk('public')->exists($clientAudit->document)) {
            Alert::error('Erreur', 'Le document demandé est introuvable.'); // ← Alert d’erreur
            return back();
        }

        $path = Storage::disk('public')->path($clientAudit->document);
        return response()->download($path, basename($path));
    }

    /**

     * Génère la fiche Client Audit au format PDF.

     * @param ClientAudit $clientAudit

     * @return \Illuminate\Http\Response

     */

    public function generatePdf(ClientAudit $clientAudit)

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

        // Assurez-vous que le nom de la vue correspond à l'emplacement réel (ex: 'clients_audits.pdf')

        $pdf = Pdf::loadView('pdf.client', [

            'clientAudit' => $clientAudit,

            'companySetting' => $companySetting,

            'logoBase64' => $logoBase64,

        ]);



        // 4. Affichage du PDF dans le navigateur

        $filename = 'Fiche_Client_Audit_' . $clientAudit->nom_client . '.pdf';



        return $pdf->stream($filename);
    }
}
