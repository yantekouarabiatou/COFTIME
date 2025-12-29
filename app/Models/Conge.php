<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conge extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_conge',
        'date_debut',
        'date_fin',
        'user_id',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'type_conge' => 'string',
    ];

    // Liste des valeurs possibles pour type_conge
    const TYPES = ['MALADIE', 'MATERNITE', 'REMUNERE', 'NON REMUNERE'];

    /**
     * Un congé appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
