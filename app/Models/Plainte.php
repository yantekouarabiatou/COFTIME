<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plainte extends Model
{
    use HasFactory;

    protected $table = 'plaintes'; // Si tu veux garder la table au pluriel

    protected $fillable = [
        'Reference',
        'dates',
        'motif_plainte',
        'nom_client',
        'requete_client',
        'action_mener',
        'action_entreprises',
        'communication_personnel',
        'etat_plainte',
        'user_id',
        'document',
    ];

    protected $casts = [
        'dates' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignations()
    {
        return $this->hasMany(Assignation::class);
    }

    // Auto-remplissage du user_id
    protected static function booted()
    {
        static::creating(function ($plainte) {
            $plainte->user_id = auth()->id();
        });

        static::updating(function ($plainte) {
            $plainte->user_id = auth()->id();
        });
    }

    // Tes scopes
    public function scopeEnCours($query)   { return $query->where('etat_plainte', 'En cours'); }
    public function scopeResolues($query)  { return $query->where('etat_plainte', 'Résolue'); }
    public function scopeFermees($query)   { return $query->where('etat_plainte', 'Fermée'); }
}
