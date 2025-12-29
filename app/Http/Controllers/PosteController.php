<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poste;
use Exception;
use RealRashid\SweetAlert\Facades\Alert;

class PosteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $postes = Poste::orderBy('intitule')->get(); // Tous les postes pour le select
        return view('pages.postes.index', compact('postes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'intitule' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            Poste::create($validated);

            Alert::success('Succès', "Poste créé avec succès.");
            return back()->with('success', 'Poste créé avec succès.');

        } catch (\Exception $e) {

            Alert::error('Erreur', "Une erreur est survenue lors de la création du poste.");
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Poste $poste)
    {
        $validated = $request->validate([
            'intitule' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            $poste->update($validated);

            Alert::success('Succès', "Poste modifié avec succès.");
            return back()->with('success', 'Poste modifié avec succès.');

        } catch (\Exception $e) {

            Alert::error('Erreur', "Impossible de modifier le poste.");
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Poste $poste)
    {
        try {
            $poste->delete();

            Alert::success('Succès', "Poste supprimé avec succès.");
            return back()->with('success', 'Poste supprimé.');

        } catch (\Exception $e) {

            Alert::error('Erreur', "Impossible de supprimer ce poste.");
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}
