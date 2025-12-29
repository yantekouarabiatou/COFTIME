<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dossier extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'client_id'];

    /**
     * Un dossier appartient à un client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Un dossier a plusieurs entrées de temps
     */
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }
}
