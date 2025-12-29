<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'slogan',
        'email',
        'telephone',
        'adresse',
        'logo',
        'site_web',
        'ville',
        'pays',
    ];

    // Accessor pour l'image
    public function getLogoUrlAttribute()
    {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : asset('assets/img/logo_cofima_bon.jpg');
    }
}
