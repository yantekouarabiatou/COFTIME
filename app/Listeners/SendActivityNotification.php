<?php

namespace App\Listeners;

use App\Events\ModelActivityEvent;
use App\Notifications\ActivityNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class SendActivityNotification
{
    public function handle(ModelActivityEvent $event)
    {
        try {
            Log::info('SendActivityNotification déclenché', [
                'model' => $event->model ? get_class($event->model) : 'null',
                'model_id' => $event->model?->id,
                'action' => $event->action,
            ]);

            // 1. Admins
            $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['super-admin', 'admin']))->get();

            // 2. Utilisateurs spécifiques (auteur, assigné, etc.)
            $specificUsers = $this->getSpecificRecipients($event);

            // CORRECTION #1 : Plus de concat() qui casse tout !
            $recipients = collect()->merge($admins)->merge($specificUsers);

            // CORRECTION #2 : Gestion propre des additionalRecipients
            if (!empty($event->additionalRecipients)) {
                foreach ($event->additionalRecipients as $recipient) {
                    if ($recipient instanceof User) {
                        $recipients->push($recipient);
                    } elseif (is_numeric($recipient)) {
                        $user = User::find($recipient);
                        if ($user) $recipients->push($user);
                    }
                }
            }

            // Exclure l'utilisateur courant (sauf pour certaines actions)
            $currentUserId = auth()->id();
            if ($currentUserId && $this->shouldExcludeCurrentUser($event->action)) {
                $recipients = $recipients->filter(fn($u) => $u->id !== $currentUserId);
            }

            // Nettoyage final
            $recipients = $recipients->unique('id')->values();

            Log::info('Destinataires finaux', [
                'total' => $recipients->count(),
                'emails' => $recipients->pluck('email')->toArray(),
            ]);

            if ($recipients->isEmpty()) {
                Log::warning('Aucun destinataire trouvé pour la notification');
                return;
            }

            // Envoi
            foreach ($recipients as $user) {
                try {
                    $user->notify(new ActivityNotification(
                        $event->model,
                        $event->action,
                        $event->customMessage
                    ));
                    Log::info('Notification envoyée', ['email' => $user->email]);
                } catch (\Exception $e) {
                    Log::error('Échec envoi notification', ['email' => $user->email, 'error' => $e->getMessage()]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Erreur critique SendActivityNotification', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function getSpecificRecipients(ModelActivityEvent $event): Collection
    {
        if (!$event->model) return collect();

        // Précharger les relations utiles pour éviter N+1
        if (method_exists($event->model, 'loadMissing')) {
            match (true) {
                $event->model instanceof \App\Models\Plainte => $event->model->loadMissing(['user', 'assignations.user']),
                $event->model instanceof \App\Models\Assignation => $event->model->loadMissing(['user', 'plainte.user']),
                default => null,
            };
        }

        return match (get_class($event->model)) {
            \App\Models\Plainte::class => $this->getPlainteRecipients($event->model, $event->action),
            \App\Models\Assignation::class => $this->getAssignationRecipients($event->model, $event->action),
            default => $this->getDefaultRecipients($event->model),
        };
    }

    private function getPlainteRecipients($plainte, $action): Collection
    {
        $users = collect();

        // Auteur de la plainte
        if ($plainte->user) $users->push($plainte->user);

        // Dernier utilisateur assigné
        $lastAssignation = $plainte->assignations()->latest('created_at')->first();
        if ($lastAssignation?->user) {
            $users->push($lastAssignation->user);
        }

        return $users->unique('id');
    }

    private function getAssignationRecipients($assignation, $action): Collection
    {
        $users = collect();

        // 1. L'utilisateur assigné (le plus important !)
        if ($assignation->user) {
            $users->push($assignation->user);
        }

        // 2. L'auteur de la plainte (suit son dossier)
        if ($assignation->plainte?->user) {
            $users->push($assignation->plainte->user);
        }

        return $users->unique('id');
    }

    private function getDefaultRecipients($model): Collection
    {
        $users = collect();
        if (isset($model->user_id) && $model->user_id) {
            $user = $model->user ?? User::find($model->user_id);
            if ($user) $users->push($user);
        }
        return $users;
    }

        private function shouldExcludeCurrentUser($action): bool
    {
        $currentUser = auth()->user();

        // 1. Les admins reçoivent ABSOLUMENT TOUT → jamais exclus
        if ($currentUser && $currentUser->hasRole(['super-admin', 'admin'])) {
            return false;
        }

        // 2. Pour tous les autres rôles : on les exclut UNIQUEMENT quand ils créent
        //    → ils reçoivent bien les updated, deleted, assigned, etc.
        return $action === 'created';
    }
}
