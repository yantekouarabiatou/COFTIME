<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interet extends Model
{
    use HasFactory;

    protected $fillable = [
        'details',
        'nom',
        'document',
        'date_Notification',
        'poste_id',
        'mesure_prise',
        'etat_interet',
        'user_id',
        'responsable_id'
    ];

    protected $casts = [
        'date_Notification' => 'date',
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Relation avec le poste
    public function poste()
    {
        return $this->belongsTo(Poste::class);
    }

    // Accesseurs
    public function getDateNotificationFormattedAttribute()
    {
        return $this->date_Notification?->format('d/m/Y');
    }

    public function getEtatInteretColorAttribute()
    {
        return $this->etat_interet === 'Actif' ? 'success' : 'secondary';
    }

    public function getDocumentNameAttribute()
    {
        return $this->document ? basename($this->document) : null;
    }

    public function getHasDocumentAttribute()
    {
        return !empty($this->document);
    }
}