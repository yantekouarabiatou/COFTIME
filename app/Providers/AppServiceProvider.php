<?php

namespace App\Providers;

use App\Observers\UniversalModelObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {

        Gate::before(function ($user, $ability) {

            // Liste des rôles qui ont le pouvoir absolu (God Mode)
            $rolesToutPuissants = ['super-admin', 'admin'];

            // 1. Vérification via Spatie (Méthode standard)
            if ($user->hasAnyRole($rolesToutPuissants)) {
                return true;
            }

            // 2. Vérification via votre relation role_id (Sécurité supplémentaire pour votre config)
            if ($user->role && in_array($user->role->name, $rolesToutPuissants)) {
                return true;
            }
        });
        // Enregistrez l'observateur pour les modèles spécifiques


        Blade::directive('adjustBrightness', function ($expression) {
            return "<?php echo adjustBrightness{$expression}; ?>";
        });
    }
}
