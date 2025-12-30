<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_entry_id',
        'user_id',
        'dossier_id',
        'heure_debut',
        'heure_fin',
        'heures_reelles',
        'travaux',
    ];

    protected $casts = [
        'heure_debut'    => 'datetime:H:i:s',
        'heure_fin'      => 'datetime:H:i:s',
        'heures_reelles' => 'decimal:2',
    ];

    // ------------------------------------------------------------------
    // Boot Method - Recalcul automatique du DailyEntry parent
    // ------------------------------------------------------------------

    protected static function boot()
    {
        parent::boot();

        // Après création ou mise à jour d'une TimeEntry
        static::saved(function ($timeEntry) {
            if ($timeEntry->dailyEntry) {
                $timeEntry->dailyEntry->recalculerHeuresReelles();
            }
        });

        // Après suppression d'une TimeEntry
        static::deleted(function ($timeEntry) {
            if ($timeEntry->dailyEntry) {
                $timeEntry->dailyEntry->recalculerHeuresReelles();
            }
        });
    }

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------

    public function dailyEntry()
    {
        return $this->belongsTo(DailyEntry::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }

    // ------------------------------------------------------------------
    // Accessors
    // ------------------------------------------------------------------

    /**
     * Durée formatée (ex: 3.50h)
     */
    public function getHeuresFormateesAttribute()
    {
        return number_format((float) $this->heures_reelles, 2) . 'h';
    }

    /**
     * Plage horaire lisible (ex: 09:00 - 12:00)
     */
    public function getPlageAttribute()
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return '-';
        }

        return $this->heure_debut->format('H:i') . ' - ' . $this->heure_fin->format('H:i');
    }

    /**
     * Calcule la durée en heures entre heure_debut et heure_fin
     */
    public function getDureeCalculeeAttribute()
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return 0;
        }

        $minutes = $this->heure_debut->diffInMinutes($this->heure_fin);
        return round($minutes / 60, 2);
    }
}
