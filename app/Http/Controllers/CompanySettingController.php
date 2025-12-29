<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    /**
     * Affiche les paramètres de l'entreprise.
     * @return \Illuminate\View\View
     */
    public function show()
    {
        // Récupère l'unique ligne de paramètres (ou crée un nouvel objet vide)
        $setting = CompanySetting::firstOrNew(['id' => 1]);

        return view('company_settings.show', compact('setting'));
    }

    /**
     * Affiche le formulaire de modification des paramètres.
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        // Récupère l'unique ligne de paramètres (ou crée un nouvel objet vide)
        $setting = CompanySetting::firstOrNew(['id' => 1]);

        return view('company_settings.edit', compact('setting'));
    }

    /**
     * Met à jour les paramètres de l'entreprise.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanySetting  $companySetting
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, CompanySetting $setting)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
            'site_web' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 2MB max
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo si un nouveau est téléchargé
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }

            // Stocker le nouveau logo
            $data['logo'] = $request->file('logo')->store('company', 'public');
        }

        // Met à jour l'unique ligne (ou la crée si elle n'existe pas)
        $setting->update($data);

        return redirect()->route('settings.show')
                         ->with('success', 'Les paramètres de l\'entreprise ont été mis à jour avec succès.');
    }
}
