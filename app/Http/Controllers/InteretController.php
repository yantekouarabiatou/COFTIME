<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Interet;
use App\Models\User;
use App\Models\Poste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;




use function Illuminate\Log\log;

class InteretController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Lecture (Liste, Détails, PDF, Téléchargement doc, Stats, Recherche)
        // Note: J'inclus 'search', 'statistics' et 'exportPdf' dans la permission de voir
        $this->middleware('permission:voir les conflits d\'intérêts')
             ->only(['index', 'show', 'download', 'generatePdf', 'search', 'statistics', 'exportPdf']);

        // Création (Formulaire, Stockage, Duplication)
        $this->middleware('permission:créer des conflits d\'intérêts')
             ->only(['create', 'store', 'duplicate']);

        // Modification (Formulaire, Update, Changement de statut)
        $this->middleware('permission:modifier des conflits d\'intérêts')
             ->only(['edit', 'update', 'toggleStatus']);

        // Suppression
        $this->middleware('permission:supprimer des conflits d\'intérêts')
             ->only(['destroy']);
    }
    /**
     * Afficher la liste des intérêts
     */
    public function index()
    {
        $interets = Interet::with(['user', 'poste'])
            ->latest()
            ->get();

        $postes = Poste::orderBy('intitule')->get(); // Récupérer tous les postes

        return view('pages.interets.index', compact('interets', 'postes'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $users = User::orderBy('nom')->get();
        $postes = Poste::orderBy('intitule')->get(); // Récupérer tous les postes

        return view('pages.interets.create', compact('users', 'postes'));
    }

    /**
     * Stocker un nouvel intérêt
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'details' => 'required|string',
            'nom' => 'required|string|max:255',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'date_Notification' => 'nullable|date',
            'poste_id' => 'nullable|exists:postes,id',
            'mesure_prise' => 'nullable|string',
            'etat_interet' => 'required|in:Actif,Inactif',
            'responsable_id' => 'required|exists:users,id'
        ]);

        try {

            // Gestion du document
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('documents/interets', 'public');
                $validated['document'] = $documentPath;
            } else {
                $validated['document'] = null;
            }

            // Ajout du user connecté
            $validated['user_id'] = auth()->id();

            // Enregistrement
            Interet::create($validated);

            Alert::success('Succès !', 'Intérêt créé avec succès.');
            return redirect()->route('interets.index');

        } catch (\Exception $e) {

            Log::error("Erreur création intérêt : " . $e->getMessage());

            Alert::error('Erreur !', 'Erreur lors de la création: ' . $e->getMessage());
            return back()->withInput();
        }
    }


    /**
     * Afficher les détails d'un intérêt
     */
    public function show(Interet $interet)
    {
        $interet->load(['user', 'poste']);
        return view('pages.interets.show', compact('interet'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Interet $interet)
    {
        $users = User::orderBy('nom')->get();
        $postes = Poste::orderBy('intitule')->get(); // Récupérer tous les postes

        return view('pages.interets.edit', compact('interet', 'users', 'postes'));
    }

    /**
     * Mettre à jour un intérêt
     */
    public function update(Request $request, Interet $interet)
    {
        $validated = $request->validate([
            'details'           => 'required|string',
            'nom'               => 'required|string|max:255',
            'document'          => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'date_Notification' => 'nullable|date',
            'poste_id'          => 'nullable|exists:postes,id',
            'mesure_prise'      => 'nullable|string',
            'etat_interet'      => 'required|in:Actif,Inactif',
            'responsable_id'           => 'required|exists:users,id'
        ]);

        try {
            // Gestion du document
            if ($request->hasFile('document')) {
                // Supprimer l'ancien document
                if ($interet->document && Storage::disk('public')->exists($interet->document)) {
                    Storage::disk('public')->delete($interet->document);
                }

                $documentPath = $request->file('document')->store('documents/interets', 'public');
                $validated['document'] = $documentPath;
            }
            // Sinon : on garde l'ancien document → rien à faire, il est déjà dans $interet->document
            // On ne touche pas à $validated['document'] si pas de nouveau fichier

            // SAUVEGARDE DES DONNÉES (cette ligne manquait !)
            $interet->update($validated);

            Alert::success('Succès !', 'Intérêt modifié avec succès.');

            return redirect()->route('interets.index');
        } catch (\Exception $e) {
            Alert::error('Erreur !', 'Une erreur est survenue lors de la modification.');
            log::error('Erreur update intérêt: ' . $e->getMessage());

            return back()->withInput();
        }
    }

    /**
     * Supprimer un intérêt
     */
    public function destroy(Interet $interet)
    {
        try {
            // Supprimer le document associé s'il existe
            if ($interet->document) {
                Storage::disk('public')->delete($interet->document);
            }

            $interet->delete();

            Alert::success('Succès', 'Intérêt supprimé avec succès!');
            return redirect()->route('interets.index');
        } catch (\Exception $e) {
            Alert::error('Erreur', 'Erreur lors de la suppression: ' . $e->getMessage());
            return back();
        }
    }


    /**
     * Télécharger le document
     */
    public function download(Interet $interet)
    {
        try {
            if (!$interet->document) {
                return back()->with('error', 'Aucun document disponible pour cet intérêt.');
            }

            return Storage::disk('public')->download($interet->document);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du téléchargement: ' . $e->getMessage());
        }
    }

    /**
     * Changer l'état d'un intérêt
     */
    public function toggleStatus(Interet $interet)
    {
        try {
            $newStatus = $interet->etat_interet === 'Actif' ? 'Inactif' : 'Actif';
            $interet->update(['etat_interet' => $newStatus]);

            return back()->with('success', "Statut changé à {$newStatus} avec succès!");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du changement de statut: ' . $e->getMessage());
        }
    }

    /**
     * Rechercher des intérêts
     */
    public function search(Request $request)
    {
        $search = $request->get('search');

        $interets = Interet::with(['user', 'poste'])
            ->where('nom', 'like', "%{$search}%")
            ->orWhere('details', 'like', "%{$search}%")
            ->orWhere('mesure_prise', 'like', "%{$search}%")
            ->orWhereHas('user', function ($query) use ($search) {
                $query->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%");
            })
            ->orWhereHas('poste', function ($query) use ($search) {
                $query->where('libelle', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        $postes = Poste::orderBy('libelle')->get(); // Ajouter cette ligne

        return view('pages.interets.index', compact('interets', 'postes', 'search'));
    }

    /**
     * Afficher les statistiques
     */
    public function statistics()
    {
        $totalInterets = Interet::count();
        $interetsActifs = Interet::where('etat_interet', 'Actif')->count();
        $interetsInactifs = Interet::where('etat_interet', 'Inactif')->count();
        $interetsRecent = Interet::where('created_at', '>=', now()->subDays(30))->count();

        $interetsByUser = Interet::with('user')
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->get();

        $interetsByPoste = Interet::with('poste')
            ->selectRaw('poste_id, COUNT(*) as count')
            ->groupBy('poste_id')
            ->get();

        return view('interets.statistics', compact(
            'totalInterets',
            'interetsActifs',
            'interetsInactifs',
            'interetsRecent',
            'interetsByUser',
            'interetsByPoste'
        ));
    }

    /**
     * Dupliquer un intérêt
     */
    public function duplicate(Interet $interet)
    {
        try {
            $newInteret = $interet->replicate();
            $newInteret->nom = $interet->nom . ' (Copie)';
            $newInteret->document = null; // Ne pas dupliquer le document
            $newInteret->created_at = now();
            $newInteret->updated_at = now();
            $newInteret->save();

            return redirect()->route('interets.edit', $newInteret)
                ->with('success', 'Intérêt dupliqué avec succès! Vous pouvez maintenant le modifier.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la duplication: ' . $e->getMessage());
        }
    }

    /**
     * Exporter en PDF
     */
    public function exportPdf(Interet $interet)
    {
        try {
            $interet->load(['user', 'poste']);

            // Logique d'export PDF à implémenter
            // return PDF::loadView('interets.pdf', compact('interet'))->download();

            return back()->with('info', 'Fonctionnalité d\'export PDF en cours de développement.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    /**

     * Génère la fiche de Déclaration de Conflit d'Intérêt au format PDF.

     * @param Interet $interet

     * @return \Illuminate\Http\Response

     */

    public function generatePdf(Interet $interet)

    {

        // 1. Fonction d'aide locale pour l'encodage Base64

        // (Nécessaire pour les images stockées dans /storage/app/public/ car DOMPDF ne les lit pas directement)

        $getAssetBase64 = function ($path) {

            if (!$path) { return null; }



            try {

                $fullPath = Storage::disk('public')->path($path);
                if (file_exists($fullPath)) {

                    $imageData = file_get_contents($fullPath);

                    $mimeType = mime_content_type($fullPath);

                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

                }

            } catch (\Exception $e) {

                // Gestion des erreurs de lecture de fichier

                return null;

            }

            return null;

        };



        // 2. Récupération des données globales

        $companySetting = CompanySetting::first();

        $logoBase64 = $companySetting && $companySetting->logo

                      ? $getAssetBase64($companySetting->logo)

                      : null;

        // 4. Définition de la vue et passage des données

        $pdf = Pdf::loadView('pdf.interet', [

            'interet' => $interet,

            'companySetting' => $companySetting,

            'logoBase64' => $logoBase64,

        ]);



        // 5. Affichage du PDF dans le navigateur

        $filename = 'Conflit_Interet_N' . $interet->id . '.pdf';



        return $pdf->stream($filename);

    }


}
