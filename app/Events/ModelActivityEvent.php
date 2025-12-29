<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelActivityEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $model;
    public $action;
    public $customMessage;
    public $additionalRecipients; // Nouveau: pour les utilisateurs spécifiques

    public function __construct($model, string $action, ?string $customMessage = null, array $additionalRecipients = [])
    {
        $this->model = $model;
        $this->action = $action;
        $this->customMessage = $customMessage;
        $this->additionalRecipients = $additionalRecipients;
    }
}
