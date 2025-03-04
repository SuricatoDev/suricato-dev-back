<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passageiro extends Model
{
    use HasFactory;

    protected $table = 'passageiros';

    protected $fillable = [
        'user_id',
        'nome',
        'cpf',
        'rg',
        'data_nascimento',
    ];

/************************* RELACIONAMENTOS ************************ */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function caravanaPassageiros()
    {
        return $this->hasMany(CaravanaPassageiro::class);
    }

    public function avaliacao()
    {
        return $this->hasMany(Avaliacao::class);
    }

    public function denuncia()
    {
        return $this->hasMany(Denuncia::class);
    }

}
