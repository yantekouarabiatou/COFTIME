<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_entry_id',
        'heures',
        'travaux',
        'heure_debut',
        'heure_fin',
        'user_id',
        'dossier_id',
    ];

    protected $casts = [
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i',
    ];

    /**
     * Une entrée de temps appartient à une entrée journalière
     */
    public function dailyEntry()
    {
        return $this->belongsTo(DailyEntry::class);
    }

    /**
     * Une entrée de temps appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Une entrée de temps appartient à un dossier
     */
    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }
}
