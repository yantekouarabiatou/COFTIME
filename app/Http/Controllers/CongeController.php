<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\User;
use RealRashid\SweetAlert\Facades\Alert; // ← AJOUTÉ
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CongeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $user = Auth::user();

        $conges = Conge::with('user')
            ->when(!$user->hasRole('admin'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        return view('pages.conges.index', compact('conges'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('pages.conges.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // 1. Validation
            $request->validate([
                'type_conge' => 'required|in:MALADIE,MATERNITE,REMUNERE,NON REMUNERE',
                'date_debut' => 'required|date',
                'date_fin'   => 'required|date|after_or_equal:date_debut',
            ], [
                'type_conge.required' => 'Le type de congé est obligatoire.',
                'type_conge.in' => 'Type de congé invalide.',
                'date_debut.required' => 'La date de début est obligatoire.',
                'date_fin.required' => 'La date de fin est obligatoire.',
                'date_fin.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.',
            ]);

            // 2. Création du congé
            Conge::create([
                'type_conge' => $request->type_conge,
                'date_debut' => $request->date_debut,
                'date_fin'   => $request->date_fin,
                'user_id'    => Auth::id(),
            ]);

            // 3. SweetAlert succès
            Alert::success('Succès', 'Votre congé a été enregistrée avec succès.');

            return redirect()->route('conges.index');

        } catch (Exception $e) {

            // SweetAlert erreur
            Alert::error('Erreur', 'Une erreur est survenue lors de l’enregistrement du congé.');

            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Conge $conge)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conge $conge)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conge $conge)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conge $conge)
    {
        //
    }
}
