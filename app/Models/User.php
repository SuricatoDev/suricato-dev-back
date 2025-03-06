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
        'email',
        'password',
        'endereco',
        'cep',
        'cidade_id',
        'telefone',
        'tipo',
        'ativo',
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

    public function passageiro()
    {
        return $this->hasOne(Passageiro::class);
    }

    public function organizador()
    {
        return $this->hasOne(Organizador::class);
    }

    public function suporte()
    {
        return $this->hasMany(Suporte::class);
    }

    public function denuncia()
    {
        return $this->hasMany(Denuncia::class);
    }
}
