<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ActivityNotification extends Notification
{
    use Queueable;

    protected $model;
    protected $action;
    protected $customMessage;
    protected $modelClass;
    protected $modelId;

    public function __construct($model, string $action = 'created', ?string $customMessage = null)
    {
        $this->model = $model;
        $this->action = $action;
        $this->customMessage = $customMessage;
        $this->modelClass = get_class($model);
        $this->modelId = $model->id ?? null;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $causer = auth()->user()?->full_name ?? 'Système';
        $ref = $this->getReference();

        $message = $this->customMessage ?? match ($this->action) {
            'created'  => "$causer a créé $ref",
            'updated'  => "$causer a modifié $ref",
            'deleted'  => "$causer a supprimé $ref",
            'assigned' => "$causer vous a assigné à $ref",
            default    => "$causer a effectué une action sur $ref",
        };

        // STRUCTURE CRITIQUE : Doit correspondre à ce que la navbar attend
        return [
            'message'   => $message,
            'url'       => $this->getUrl(),
            'icon'      => $this->getIcon(),
            'color'     => $this->getColor(),
            'action'    => $this->action,
            'model_type' => class_basename($this->modelClass),
            'model_id'   => $this->modelId,
        ];
    }

    private function getReference(): string
    {
        // Pour les modèles supprimés, on utilise les propriétés sauvegardées
        if (!$this->model || !$this->model->exists) {
            return class_basename($this->modelClass) . ' #' . $this->modelId;
        }

        return $this->model->Reference
            ?? $this->model->nom
            ?? $this->model->nom_client
            ?? $this->model->titre
            ?? $this->model->intitule
            ?? class_basename($this->model) . ' #' . $this->model->id;
    }

    private function getUrl(): ?string
    {
        // Pour les modèles supprimés, pas d'URL
        if (!$this->model || !$this->model->exists || in_array($this->action, ['deleted', 'force_deleted'])) {
            return null;
        }

        return match (true) {
            $this->model instanceof \App\Models\Plainte        => route('plaintes.show', $this->model),
            $this->model instanceof \App\Models\Interet        => route('interets.show', $this->model),
            $this->model instanceof \App\Models\ClientAudit    => route('clients-audit.show', $this->model),
            $this->model instanceof \App\Models\CadeauInvitation => route('cadeau-invitations.show', $this->model),
            $this->model instanceof \App\Models\Independance   => route('independances.show', $this->model),

            // CORRECTION ICI : Assignation → redirige vers la plainte
            $this->model instanceof \App\Models\Assignation    => $this->model->plainte
                ? route('plaintes.show', $this->model->plainte)
                : route('plaintes.index'),

            $this->model instanceof \App\Models\Poste          => route('postes.show', $this->model),
            default                                             => null,
        };
    }

    private function getIcon(): string
    {
        return match ($this->action) {
            'created'  => 'fas fa-plus-circle',
            'updated'  => 'fas fa-edit',
            'deleted'  => 'fas fa-trash',
            'assigned' => 'fas fa-user-tag',
            default    => 'fas fa-bell',
        };
    }

    private function getColor(): string
    {
        return match ($this->action) {
            'created'  => 'bg-success',
            'updated'  => 'bg-warning',
            'deleted'  => 'bg-danger',
            'assigned' => 'bg-info',
            default    => 'bg-primary',
        };
    }
}
