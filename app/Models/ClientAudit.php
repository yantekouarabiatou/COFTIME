<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAudit extends Model
{
    use HasFactory;

    protected $table = 'clients_audits';

    protected $fillable = [
        'nom_client',
        'adresse',
        'document',
        'siege_social',
        'frais_audit',
        'frais_autres',
        'user_id',
    ];

    protected $casts = [
        'frais_audit' => 'decimal:2',
        'frais_autres' => 'decimal:2',
    ];

    /**
     * Relation avec l'utilisateur créateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
  
    /**
     * Accesseur pour le total des frais
     */
    public function getTotalFraisAttribute(): float
    {
        return ($this->frais_audit ?? 0) + ($this->frais_autres ?? 0);
    }


    /**
     * Scope pour les clients en cours
     */
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    /**
     * Scope pour les clients inactifs
     */
    public function scopeInactifs($query)
    {
        return $query->where('statut', 'inactif');
    }
}
