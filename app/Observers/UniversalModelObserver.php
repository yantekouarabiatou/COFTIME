<?php

namespace App\Observers;

use App\Events\ModelActivityEvent;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UniversalModelObserver
{
    /**
     * Événement lors de la création
     */ public function created(Model $model)
    {
        $this->logActivity('created', $model);

        $action = $model instanceof \App\Models\Assignation ? 'assigned' : 'created';
        $this->notifyUsers($model, $action);
    }

    public function updated(Model $model)
    {
        $oldValues = $model->getOriginal();
        $newValues = $model->getChanges();

        $this->logActivity('updated', $model, "Modification de " . class_basename($model), $oldValues, $newValues);

        $action = 'updated';
        if ($model instanceof \App\Models\Plainte && isset($newValues['etat_plainte'])) {
            $action = 'status_changed';
        }

        $additionalRecipients = $this->getAdditionalRecipientsForUpdate($model, $oldValues, $newValues);
        $this->notifyUsers($model, $action, $additionalRecipients);
    }

    /**
     * Événement lors de la suppression
     */
    public function deleted(Model $model)
    {
        $this->logActivity('deleted', $model);
        $this->notifyUsers($model, 'deleted');
    }

    /**
     * Événement lors de la restauration
     */
    public function restored(Model $model)
    {
        $this->logActivity('restored', $model);
        $this->notifyUsers($model, 'restored');
    }

    /**
     * Événement lors de la suppression définitive
     */
    public function forceDeleted(Model $model)
    {
        $this->logActivity('force_deleted', $model);
        $this->notifyUsers($model, 'force_deleted');
    }

    /**
     * Log l'activité
     */
    private function logActivity(string $action, Model $model, string $description = null, array $old = null, array $new = null)
    {
        try {
            ActivityLogger::log($action, $model, $description, $old, $new);
        } catch (\Exception $e) {
            Log::error("Erreur lors du log d'activité: " . $e->getMessage(), [
                'action' => $action,
                'model' => get_class($model),
                'model_id' => $model->id
            ]);
        }
    }

    /**
     * Notifier les utilisateurs
     */
    private function notifyUsers(Model $model, string $action, array $additionalRecipients = [])
    {
        try {
            // CHARGEMENT AUTOMATIQUE POUR TOUS LES MODÈLES QUI ONT BESOIN DE user
            $model->loadMissing('user'); // ← pour tous les modèles qui ont user_id

            // Cas spécifiques (qui ont besoin de plus)
            if ($model instanceof \App\Models\Assignation) {
                $model->loadMissing(['user', 'plainte.user']);
            }
            if ($model instanceof \App\Models\Plainte) {
                $model->loadMissing(['user', 'assignations.user']);
            }

            event(new ModelActivityEvent($model, $action, null, $additionalRecipients));
        } catch (\Exception $e) {
            Log::error("Erreur notification: " . $e->getMessage());
        }
    }

    /**
     * Obtenir des destinataires supplémentaires pour les modifications
     */
    private function getAdditionalRecipientsForUpdate(Model $model, array $oldValues, array $newValues)
    {
        $recipients = [];

        // Exemple: Si changement d'utilisateur assigné dans une plainte
        if ($model instanceof \App\Models\Plainte && isset($newValues['user_assigned_id'])) {
            $oldAssignedId = $oldValues['user_assigned_id'] ?? null;
            $newAssignedId = $newValues['user_assigned_id'];

            if ($oldAssignedId != $newAssignedId && $newAssignedId) {
                $newUser = \App\Models\User::find($newAssignedId);
                if ($newUser) {
                    $recipients[] = $newUser;
                }
            }
        }

        // Ajoutez d'autres conditions spécifiques selon vos besoins

        return $recipients;
    }
}
