<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passageiro extends Model
{
    use HasFactory;

    protected $table = 'passageiros';

    protected $fillable = [
        'id',
        'cpf',
        'rg',
        'contato_emergencia',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cep',
        'cidade',
        'estado',
    ];

/************************* RELACIONAMENTOS ************************ */

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public function caravanaPassageiros()
    {
        return $this->hasMany(CaravanaPassageiro::class);
    }

    public function avaliacao()
    {
        return $this->hasMany(Avaliacao::class);
    }

}
