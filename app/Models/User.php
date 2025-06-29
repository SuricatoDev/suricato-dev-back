<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nome',
        'email',
        'email_verified_at',
        'verificado',
        'email_verification_token',
        'password',
        'data_nascimento',
        'telefone',
        'passageiro',
        'organizador',
        'ativo',
        'foto_perfil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /************************* RELACIONAMENTOS *************************/

    public function favoritos()
    {
        return $this->hasMany(Favorito::class);
    }

    public function organizador()
    {
        return $this->hasOne(Organizador::class, 'id', 'id');
    }

    public function passageiro()
    {
        return $this->hasOne(Passageiro::class, 'id', 'id');
    }

    public function suporte()
    {
        return $this->hasMany(Suporte::class);
    }
}
