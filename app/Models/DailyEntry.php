<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jour',
        'heures_theoriques',
        'heures_totales',
        'is_weekend',
        'is_holiday',
        'commentaire',
    ];

    protected $casts = [
        'jour' => 'date',
        'is_weekend' => 'boolean',
        'is_holiday' => 'boolean',
    ];

    /**
     * Une entrée journalière appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Une entrée journalière a plusieurs entrées de temps
     */
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }
}
