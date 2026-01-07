<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jour',
        'heures_theoriques',
        'heures_reelles',
        'commentaire',
        'statut',
        'valide_par',
        'valide_le',
        'motif_refus',
    ];

    protected $casts = [
        'jour'              => 'date:Y-m-d',
        'valide_le'         => 'datetime',
        'heures_theoriques' => 'decimal:2',
        'heures_reelles'    => 'decimal:2',
    ];

    // ------------------------------------------------------------------
    // Boot Method - Calcul automatique des heures réelles
    // ------------------------------------------------------------------

    protected static function boot()
    {
        parent::boot();

        // Recalculer les heures réelles après chaque sauvegarde de TimeEntry
        static::saved(function ($dailyEntry) {
            $dailyEntry->recalculerHeuresReelles();
        });
    }

    /**
     * Recalcule et met à jour les heures_reelles en fonction des TimeEntry
     */
    public function recalculerHeuresReelles()
    {
        $total = $this->timeEntries()->sum('heures_reelles');

        // Éviter une boucle infinie en vérifiant si la valeur a changé
        if ($this->heures_reelles != $total) {
            $this->updateQuietly(['heures_reelles' => $total]);
        }
    }

    // ------------------------------------------------------------------
    // Relations
    // ------------------------------------------------------------------

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // ------------------------------------------------------------------
    // Accessors & Mutators
    // ------------------------------------------------------------------

    /**
     * Écart entre heures réelles et théoriques
     */
    public function getEcartAttribute()
    {
        return $this->heures_reelles - $this->heures_theoriques;
    }

    /**
     * Pourcentage de réalisation
     */
    public function getTauxRealisationAttribute()
    {
        if ($this->heures_theoriques <= 0) {
            return 0;
        }

        return round(($this->heures_reelles / $this->heures_theoriques) * 100, 1);
    }

    /**
     * Badge HTML pour le statut
     */
    public function getStatutBadgeAttribute()
    {
        $badges = [
            'soumis' => 'info',
            'validé' => 'success',
            'refusé' => 'danger',
        ];

        $color = $badges[$this->statut] ?? 'secondary';

        return '<span class="badge badge-' . $color . '">' . ucfirst($this->statut) . '</span>';
    }

    /**
     * Indique si la journée est un week-end
     */
    public function getIsWeekendAttribute()
    {
        return $this->jour->isWeekend();
    }

    /**
     * Indique si la journée est un jour férié
     */
    public function getIsHolidayAttribute()
    {
        $holidays = [
            '01-01', // Nouvel an
            '05-01', // Fête du travail
            '05-08', // Victoire 1945
            '07-14', // Fête nationale
            '08-15', // Assomption
            '11-01', // Toussaint
            '11-11', // Armistice
            '12-25', // Noël
        ];

        $dayMonth = $this->jour->format('m-d');

        return in_array($dayMonth, $holidays);
    }

    // ------------------------------------------------------------------
    // Scopes
    // ------------------------------------------------------------------

    public function scopeSoumises($query)
    {
        return $query->where('statut', 'soumis');
    }

    public function scopeValidees($query)
    {
        return $query->where('statut', 'validé');
    }

    public function scopeRefusees($query)
    {
        return $query->where('statut', 'refusé');
    }

    public function scopePourUtilisateur($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePourPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('jour', [$debut, $fin]);
    }

}
