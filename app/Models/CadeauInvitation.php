<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CadeauInvitation extends Model
{
    use HasFactory;

    protected $table = 'cadeau_invitations';

    protected $fillable = [
        'nom',
        'date',
        'cadeau_hospitalite',
        'document',
        'description',
        'valeurs',
        'action_prise',
        'user_id',
        'responsable_id'
    ];

    protected $casts = [
        'date' => 'date',
        'valeurs' => 'decimal:2',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }


    /**
     * Accesseur pour l'action prise formatée
     */
    public function getActionPriseFormattedAttribute(): string
    {
        return match($this->action_prise) {
            'accepté' => 'Accepté',
            'refusé' => 'Refusé',
            'en_attente' => 'En attente',
            default => $this->action_prise
        };
    }

    /**
     * Accesseur pour la couleur de l'action
     */
    public function getActionPriseColorAttribute(): string
    {
        return match($this->action_prise) {
            'accepté' => 'success',
            'refusé' => 'danger',
            'en_attente' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Accesseur pour la valeur formatée
     */
    public function getValeursFormattedAttribute(): string
    {
        return $this->valeurs ? number_format($this->valeurs, 2, ',', ' ') . ' FCFA' : '0,00 FCFA';
    }
}
