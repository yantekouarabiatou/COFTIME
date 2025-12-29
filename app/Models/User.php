<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'username',
        'email',
        'photo',
        'password',
        'poste_id',
        'created_by',
        'telephone',
        'role_id',
        'is_active',
    ];


    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Accessor pour avoir le nom complet (facultatif mais pratique)
    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function poste()
    {
        return $this->belongsTo(Poste::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function plaintes()
    {
        return $this->hasMany(Plainte::class, 'user_id');
    }

    public function clientAudits()
    {
        return $this->hasMany(ClientAudit::class, 'user_id');
    }

    public function cadeauInvitations()
    {
        return $this->hasMany(CadeauInvitation::class, 'user_id');
    }
    public function cadeauInvitationRespo()
    {
        return $this->hasMany(CadeauInvitation::class, 'responsable_id');
    }
    public function interets()
    {
        return $this->hasMany(Interet::class, 'user_id');
    }

    public function independances()
    {
        return $this->hasMany(Independance::class, 'user_id');
    }

}
