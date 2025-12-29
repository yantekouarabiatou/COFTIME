<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Independance extends Model
{
    use HasFactory;

    protected $table = 'independances';

    protected $fillable = [
        'nom_client',
        'adresse',
        'siege_social',
        'type_entite',
        'frais_audit',
        'frais_non_audit',
        'honoraire_audit_exercice',
        'honoraire_audit_travail',
        'associes_mission',
        'nombres_annees_experiences',
        'question_independance',
        'actions_recquise',
        'user_id',
        'autres_services_fournit',
        'responsable_audit'
    ];

    protected $casts = [
        'frais_audit' => 'decimal:2',
        'frais_non_audit' => 'decimal:2',
        'honoraire_audit_exercice' => 'integer',
        'honoraire_audit_travail' => 'integer',
        'associes_mission' => 'array',
        'responsable_audit' => 'array',
    ];

    /**
     * Relation avec l'utilisateur
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
        return ($this->frais_audit ?? 0) + ($this->frais_non_audit ?? 0);
    }

    /**
     * Accesseur pour le total des honoraires
     */
    public function getTotalHonorairesAttribute(): int
    {
        return ($this->honoraire_audit_exercice ?? 0) + ($this->honoraire_audit_travail ?? 0);
    }

    /**
     * Accesseur pour les frais formatés
     */
    public function getFraisAuditFormattedAttribute(): string
    {
        return $this->frais_audit ? number_format($this->frais_audit, 2, ',', ' ') . ' FCFA' : '0,00 FCFA';
    }

    public function getFraisNonAuditFormattedAttribute(): string
    {
        return $this->frais_non_audit ? number_format($this->frais_non_audit, 2, ',', ' ') . ' FCFA' : '0,00 FCFA';
    }

    public function getTotalFraisFormattedAttribute(): string
    {
        return number_format($this->total_frais, 2, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour normaliser associes_mission en tableau
     */
    public function getAssociesMissionAttribute($value)
    {
        // Si c'est déjà un tableau, retourner directement
        if (is_array($value)) {
            return $value;
        }
        
        // Si c'est une chaîne JSON, décoder
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        // Si c'est une chaîne avec des IDs séparés par des virgules
        if (is_string($value) && !empty($value)) {
            return array_map('intval', explode(',', $value));
        }
        
        // Retourner un tableau vide par défaut
        return [];
    }

    /**
     * Accesseur pour normaliser responsable_audit en tableau
     */
    public function getResponsableAuditAttribute($value)
    {
        // Si c'est déjà un tableau, retourner directement
        if (is_array($value)) {
            return $value;
        }
        
        // Si c'est une chaîne JSON, décoder
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        // Si c'est une chaîne avec des IDs séparés par des virgules
        if (is_string($value) && !empty($value)) {
            return array_map('intval', explode(',', $value));
        }
        
        // Retourner un tableau vide par défaut
        return [];
    }

    /**
     * Accesseur pour les noms des associés de mission
     */
    public function getAssociesMissionNamesAttribute(): string
    {
        $ids = $this->associes_mission;
        
        if (empty($ids)) {
            return '-';
        }

        // S'assurer que c'est un tableau
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        // Récupérer les utilisateurs
        $users = User::whereIn('id', $ids)->get();

        if ($users->isEmpty()) {
            return '-';
        }

        // Construire "Nom Prénom"
        return $users->map(function ($user) {
            return trim($user->nom . ' ' . $user->prenom);
        })->join(', ');
    }

    /**
     * Accesseur pour les noms des responsables audit
     */
    public function getResponsableAuditNamesAttribute(): string
    {
        $ids = $this->responsable_audit;
        
        if (empty($ids)) {
            return '-';
        }

        // S'assurer que c'est un tableau
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        // Récupérer les utilisateurs
        $users = User::whereIn('id', $ids)->get();

        if ($users->isEmpty()) {
            return '-';
        }

        // Construire "Nom Prénom"
        return $users->map(function ($user) {
            return trim($user->nom . ' ' . $user->prenom);
        })->join(', ');
    }

    /**
     * Mutateur pour associes_mission - s'assurer que c'est un tableau
     */
    public function setAssociesMissionAttribute($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true) ?? explode(',', $value);
        }
        
        $this->attributes['associes_mission'] = json_encode((array) $value);
    }

    /**
     * Mutateur pour responsable_audit - s'assurer que c'est un tableau
     */
    public function setResponsableAuditAttribute($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true) ?? explode(',', $value);
        }
        
        $this->attributes['responsable_audit'] = json_encode((array) $value);
    }
}