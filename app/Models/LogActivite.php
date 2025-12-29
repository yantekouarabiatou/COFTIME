<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LogActivite extends Model
{
    protected $table = 'log_activites';

    protected $fillable = [
        'user_id', 'action', 'loggable_type', 'loggable_id',
        'description', 'old_values', 'new_values',
        'ip_address', 'user_agent', 'status'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation polymorphe ultra-robuste
     * Fonctionne même si :
     * - le modèle est supprimé (withTrashed)
     * - le type est "notifications"
     * - le modèle n'est pas dans une liste stricte
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo('loggable', 'loggable_type', 'loggable_id')
                    ->withTrashed()
                    ->withDefault(function () {
                        // Retourne un modèle "fantôme" propre
                        return new class extends Model {
                            public $exists = false;
                            protected $attributes = ['id' => null];
                            public function getKey() { return null; }
                            public function __toString() { return 'Ressource supprimée'; }
                        };
                    });
    }

    // === Accesseurs (100% fonctionnels même si loggable est "fantôme") ===

    public function getLoggableExistsAttribute(): bool
    {
        return $this->loggable && $this->loggable->exists;
    }

    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'created', 'create'     => 'fa-plus-circle text-success',
            'updated', 'update'     => 'fa-edit text-warning',
            'deleted', 'delete'     => 'fa-trash text-danger',
            'restored', 'restore'   => 'fa-undo text-info',
            'login'                 => 'fa-sign-in-alt text-primary',
            'logout'                => 'fa-sign-out-alt text-secondary',
            'force-deleted'         => 'fa-skull-crossbones text-dark',
            default                 => 'fa-cog text-muted',
        };
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created', 'create'     => 'success',
            'updated', 'update'     => 'warning',
            'deleted', 'delete'     => 'danger',
            'restored', 'restore'   => 'info',
            'login', 'logout'       => 'primary',
            default                 => 'secondary',
        };
    }

    public function getUrlAttribute(): ?string
    {
        if (!$this->loggable?->exists) return null;

        return match (true) {
            $this->loggable instanceof \App\Models\Poste           => route('postes.show', $this->loggable),
            $this->loggable instanceof \App\Models\User           => route('users.show', $this->loggable),
            $this->loggable instanceof \App\Models\Plainte        => route('plaintes.show', $this->loggable),
            $this->loggable instanceof \App\Models\ClientAudit    => route('clients-audit.show', $this->loggable),
            $this->loggable instanceof \App\Models\CadeauInvitation => route('cadeau-invitations.show', $this->loggable),
            $this->loggable instanceof \App\Models\Interet        => route('interets.show', $this->loggable),
            $this->loggable instanceof \App\Models\Independance   => route('independances.show', $this->loggable),
            $this->loggable instanceof \App\Models\Assignation     => route('plaintes.show', $this->loggable),
            // Ajoute les autres ici si besoin
            default                                                => null,
        };
    }

    public function getReferenceAttribute(): string
    {
        if (!$this->loggable?->exists) {
            return '<em class="text-muted">Ressource supprimée</em>';
        }

        $model = $this->loggable;

        return $model->reference
            ?? $model->nom
            ?? $model->titre
            ?? $model->intitule
            ?? $model->nom_client
            ?? trim(($model->prenom ?? '') . ' ' . ($model->nom ?? ''))
            ?? $model->email
            ?? $model->sujet
            ?? class_basename($model) . ' #' . $model->id;
    }
}
