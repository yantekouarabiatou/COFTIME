<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Plainte;
use App\Models\Assignation;
use App\Models\CompanySetting;
use App\Models\Plaintes;
use App\Models\User;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class PlaintesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Lecture (Liste, Détails et PDF)
        $this->middleware('permission:voir les plaintes')->only(['index', 'show', 'generatePdf']);

        // Création
        $this->middleware('permission:créer des plaintes')->only(['create', 'store']);

        // Modification
        $this->middleware('permission:modifier des plaintes')->only(['edit', 'update']);

        // Suppression
        $this->middleware('permission:supprimer des plaintes')->only(['destroy']);
    }

    public function index()
    {
        $plaintes = Plainte::with('user')->latest()->paginate(15);
        return view('pages.plaintes.index', compact('plaintes'));
    }

    public function show(Plainte $plainte)
    {
        $plainte->load('assignations.user');
        return view('pages.plaintes.show', compact('plainte'));
    }

    public function create()
    {
        $reference = $this->generateReference();
        $users = User::orderBy('nom')->get(); // Tous les utilisateurs pour le select
        return view('pages.plaintes.create', compact('reference', 'users'));
    }

    protected function generateReference()
    {
        // Génère une référence unique pour une plainte, vérifie l'unicité dans la table plaintes.
        do {
            $reference = 'PLT-' . strtoupper(uniqid());
        } while (Plainte::where('Reference', $reference)->exists());

        return $reference;
    }

    public function edit(Plainte $plainte)
    {
        $plainte->load('assignations.user');
        $users = User::orderBy('nom')->get();
        return view('pages.plaintes.edit', compact('plainte', 'users'));
    }

    // STORE – CORRIGÉ
    public function store(Request $request)
    {
        $request->validate([
            'Reference' => 'required|unique:plaintes,Reference',
            'dates' => 'nullable|date',
            'etat_plainte' => 'required|in:En cours,Résolue,Fermée',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10000',

            // Assignations
            'assignations.*.user_id' => 'required|exists:users,id',
            'assignations.*.fonction' => 'required|string',
            'assignations.*.signature' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'assignations.*.date' => 'required|date',
        ]);

        $data = $request->except('assignations');
        $data['user_id'] = auth()->id();

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('plaintes', 'public');
        }

        $plainte = Plainte::create($data);

        if ($request->has('assignations')) {
            foreach ($request->assignations as $index => $assign) {
                $user = User::find($assign['user_id']);

                $signaturePath = $request->hasFile("assignations.{$index}.signature")
                    ? $request->file("assignations.{$index}.signature")->store('signatures', 'public')
                    : null;

                $plainte->assignations()->create([
                    'user_id'     => $user->id,
                    'nom_prenom'  => $user->nom,        // ← Rempli automatiquement
                    'fonction'    => $assign['fonction'],
                    'signature'   => $signaturePath,
                    'date'        => $assign['date'],
                ]);
            }
        }

        return redirect()->route('plaintes.index')->with('status', 'Plainte crée avec succès !');
    }
    public function update(Request $request, Plainte $plainte)
    {
        $request->validate([
            'Reference' => 'required|unique:plaintes,Reference,' . $plainte->id,
            'dates' => 'nullable|date',
            'etat_plainte' => 'required|in:En cours,Résolue,Fermée',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10000',

            'assignations.*.user_id' => 'required|exists:users,id',
            'assignations.*.fonction' => 'required|string',
            'assignations.*.signature' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'assignations.*.date' => 'required|date',
        ]);

        // Mise à jour des champs principaux
        $data = $request->only(['Reference', 'dates', 'etat_plainte', 'nom_client', 'motif_plainte', 'requete_client', 'action_mener', 'action_entreprises', 'communication_personnel']);

        if ($request->hasFile('document')) {
            if ($plainte->document) Storage::disk('public')->delete($plainte->document);
            $data['document'] = $request->file('document')->store('plaintes', 'public');
        }

        $plainte->update($data);

        // Gestion des assignations
        $keptIds = [];

        if ($request->has('assignations')) {
            foreach ($request->assignations as $index => $assign) {
                $assignationId = $assign['id'] ?? null;
                $user = User::findOrFail($assign['user_id']);

                $dataAssign = [
                    'user_id'    => $user->id,
                    'nom_prenom' => $user->nom . ' ' . ($user->prenom ?? ''),
                    'fonction'   => $assign['fonction'],
                    'date'       => $assign['date'],
                ];

                if ($request->hasFile("assignations.{$index}.signature")) {
                    $dataAssign['signature'] = $request->file("assignations.{$index}.signature")->store('signatures', 'public');
                }

                if ($assignationId) {
                    // Mise à jour
                    $assignation = $plainte->assignations()->findOrFail($assignationId);
                    if ($request->hasFile("assignations.{$index}.signature") && $assignation->signature) {
                        Storage::disk('public')->delete($assignation->signature);
                    }
                    $assignation->update($dataAssign);
                    $keptIds[] = $assignation->id;
                } else {
                    // Création
                    $new = $plainte->assignations()->create($dataAssign);
                    $keptIds[] = $new->id;
                }
            }
        }

        // Suppression des assignations retirées
        $plainte->assignations()->whereNotIn('id', $keptIds)->each(function ($a) {
            if ($a->signature) Storage::disk('public')->delete($a->signature);
            $a->delete();
        });

        Alert::success('Succès', 'Plainte mise à jour avec succès !');
        return redirect()->route('plaintes.index');
    }


    public function destroy(Plainte $plainte)
    {
        try {
            // 1. Supprimer le document principal s'il existe
            if ($plainte->document && Storage::disk('public')->exists($plainte->document)) {
                Storage::disk('public')->delete($plainte->document);
            }

            // 2. Supprimer les signatures des assignations
            foreach ($plainte->assignations as $assignation) {
                if ($assignation->signature && Storage::disk('public')->exists($assignation->signature)) {
                    Storage::disk('public')->delete($assignation->signature);
                }
            }

            // 3. Supprimer les assignations + la plainte
            $plainte->assignations()->delete();
            $plainte->delete();

            // ALERTE DE SUCCÈS (pas d'erreur ici !)
            Alert::success('Supprimée !', 'La plainte #' . $plainte->Reference . ' a été supprimée avec succès.');

            return redirect()->route('plaintes.index');
        } catch (Exception $e) {
            // En cas d'erreur seulement
            Alert::error('Erreur', 'Impossible de supprimer la plainte. Veuillez réessayer.');

            return redirect()->back();
        }
    }

    public function generatePdf($id)
    {
        // Récupérer la plainte avec ses relations
        $plainte = Plainte::with(['assignations.user', 'user'])
            ->findOrFail($id);

        // DEBUG
        Log::info('=== GENERATION PDF ===');
        Log::info('Plainte ID: ' . $plainte->id);
        Log::info('Nombre d\'assignations: ' . $plainte->assignations->count());

        // 1. Définition de la fonction de conversion
        $getSignatureBase64 = function ($path) {
            if (!$path) {
                return null;
            }

            try {
                // Vérifier si le chemin est déjà un base64
                if (str_starts_with($path, 'data:image')) {
                    return $path;
                }

                // Utiliser Storage pour les chemins relatifs
                if (Storage::disk('public')->exists($path)) {
                    $imageData = Storage::disk('public')->get($path);
                    $mimeType = Storage::disk('public')->mimeType($path);

                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }

                // Vérifier si c'est un chemin absolu
                if (file_exists($path)) {
                    $imageData = file_get_contents($path);
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $imageData);
                    finfo_close($finfo);

                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            } catch (Exception $e) {
                Log::error('Erreur de lecture de signature: ' . $e->getMessage());
                return null;
            }

            return null;
        };

        // 2. Préparer un tableau pour toutes les assignations
        $assignationsData = [];

        foreach ($plainte->assignations as $index => $assignation) {
            // Déterminer la fonction/role
            if ($index === 0) {
                $role = 'Associé / Responsable de la mission';
            } elseif ($index === 1) {
                $role = 'Personne responsable du traitement de la plainte';
            } else {
                $role = 'Personne impliquée ' . ($index - 1); // ou autre logique
            }

            // Récupérer la signature
            $signature = null;
            if ($assignation->signature) {
                $signature = $getSignatureBase64($assignation->signature);
            } elseif ($assignation->user && $assignation->user->signature) {
                $signature = $getSignatureBase64($assignation->user->signature);
            }

            $assignationsData[] = [
                'assignation' => $assignation,
                'role' => $role,
                'signature' => $signature,
                'index' => $index + 1,
            ];
        }

        // 3. Si aucune assignation, créer une ligne par défaut
        if (empty($assignationsData)) {
            $assignationsData[] = [
                'assignation' => (object) [
                    'nom_prenom' => $plainte->user->name ?? 'À désigner',
                    'fonction' => 'Associé / Responsable de la mission',
                    'date' => null,
                ],
                'role' => 'Associé / Responsable de la mission',
                'signature' => null,
                'index' => 1,
            ];

            $assignationsData[] = [
                'assignation' => (object) [
                    'nom_prenom' => 'À désigner',
                    'fonction' => 'Personne responsable du traitement de la plainte',
                    'date' => null,
                ],
                'role' => 'Personne responsable du traitement de la plainte',
                'signature' => null,
                'index' => 2,
            ];
        }

        // 4. Récupération du logo de l'entreprise
        $companySetting = CompanySetting::first();
        $logoBase64 = null;

        if ($companySetting && $companySetting->logo) {
            $logoBase64 = $getSignatureBase64($companySetting->logo);
        }

        // 5. Génération du PDF
        $pdf = PDF::loadView('pdf.plaintes', [
            'plainte' => $plainte,
            'assignationsData' => $assignationsData, // Passer toutes les assignations
            'logoBase64' => $logoBase64,
        ]);

        return $pdf->stream("Fiche_Plainte_N{$plainte->id}.pdf");
    }
}
