<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignation extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     */
    protected $fillable = [
        'nom_prenom',
        'fonction',
        'signature',
        'date',
        'plainte_id',
        'user_id',
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'date' => 'date', // ou 'datetime' si tu stockes l'heure aussi
    ];

    /**
     * Une assignation appartient à une plainte.
     */
    public function plainte(): BelongsTo
    {
        return $this->belongsTo(Plainte::class);
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
