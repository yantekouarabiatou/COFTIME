<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Services\NotificationService;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'poste_id' => ['nullable', 'integer', 'exists:postes,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'poste_id' => $request->poste_id ?? null,
            // par défaut actif si non fourni
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Envoyer une notification de bienvenue à l'utilisateur créé (silencieux)
        try {
            $notifier = new NotificationService();
            $notifier->sendWelcomeNotification($user);
        } catch (\Throwable $e) {
            // Ne pas bloquer l'enregistrement si la notification échoue
            // on peut logger si besoin : logger()->error('Notification failed: '.$e->getMessage());
        }

        return redirect(route('dashboard', absolute: false));
    }
}
