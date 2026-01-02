<?php

namespace App\Observers;

use App\Events\ModelActivityEvent;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UniversalModelObserver
{
    /**
     * Création
     */
    public function created(Model $model)
    {
        $description = "Création de " . $this->getModelName($model);
        $this->logActivity('created', $model, $description);

        $this->notifyUsers($model, 'created');
    }

    /**
     * Mise à jour
     */
    public function updated(Model $model)
    {
        $changes = $model->getChanges();

        if (empty($changes)) {
            return;
        }

        // Anciennes valeurs uniquement pour les champs modifiés
        $oldValues = [];
        foreach ($changes as $key => $newValue) {
            $oldValues[$key] = $model->getOriginal($key);
        }

        $modifiedFields = implode(', ', array_keys($changes));
        $description = "Modification de " . $this->getModelName($model) . " ({$modifiedFields})";

        $this->logActivity('updated', $model, $description, $oldValues, $changes);

        $this->notifyUsers($model, 'updated');
    }

    /**
     * Suppression
     */
    public function deleted(Model $model)
    {
        $description = "Suppression de " . $this->getModelName($model);
        $this->logActivity('deleted', $model, $description);

        $this->notifyUsers($model, 'deleted');
    }

    /**
     * Restauration (soft delete)
     */
    public function restored(Model $model)
    {
        $description = "Restauration de " . $this->getModelName($model);
        $this->logActivity('restored', $model, $description);

        $this->notifyUsers($model, 'restored');
    }

    /**
     * Suppression définitive
     */
    public function forceDeleted(Model $model)
    {
        $description = "Suppression définitive de " . $this->getModelName($model);
        $this->logActivity('force_deleted', $model, $description);

        $this->notifyUsers($model, 'force_deleted');
    }

    /**
     * Log via le service
     */
    private function logActivity(string $action, Model $model, string $description = null, array $old = null, array $new = null)
    {
        try {
            ActivityLogger::log($action, $model, $description, $old, $new);
        } catch (\Exception $e) {
            Log::error("Erreur log activité (gestion temps) : " . $e->getMessage(), [
                'action' => $action,
                'model' => get_class($model),
                'model_id' => $model->getKey() ?? 'null',
            ]);
        }
    }

    /**
     * Notification via événement
     */
    private function notifyUsers(Model $model, string $action)
    {
        try {
            // Chargement des relations nécessaires selon le modèle
            match (true) {
                $model instanceof \App\Models\Conge => $model->loadMissing(['user', 'Conges.user']),
                $model instanceof \App\Models\DailyEntry => $model->loadMissing(['user', 'timeEntries.dossier.client']),
                $model instanceof \App\Models\TimeEntry  => $model->loadMissing(['user', 'dossier.client', 'dailyEntry.user']),
                $model instanceof \App\Models\Dossier    => $model->loadMissing(['client', 'timeEntries.user']),
                $model instanceof \App\Models\Client     => null,
                default => $model->loadMissing('user'),
            };

            event(new ModelActivityEvent($model, $action));
        } catch (\Exception $e) {
            Log::error("Erreur notification gestion temps : " . $e->getMessage(), [
                'model' => get_class($model),
                'action' => $action,
            ]);
        }
    }

    /**
     * Nom lisible du modèle
     */
    private function getModelName(Model $model): string
    {
        return match (true) {
            $model instanceof \App\Models\Conge => 'Congé',
            $model instanceof \App\Models\DailyEntry => 'feuille de temps',
            $model instanceof \App\Models\TimeEntry  => 'activité temps',
            $model instanceof \App\Models\Dossier    => 'dossier',
            $model instanceof \App\Models\Client     => 'client',
            default                                  => Str::lower(class_basename($model)),
        };
    }
}