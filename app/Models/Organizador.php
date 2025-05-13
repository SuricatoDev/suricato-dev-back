<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizador extends Model
{
    use HasFactory;

    protected $table = 'organizadores';

    protected $fillable = [
        'id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'cadastur',
        'inscricao_estadual',
        'inscricao_municipal',
        'telefone_comercial',
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

    public function caravanas()
    {
        return $this->hasMany(Caravana::class);
    }

    public function avaliacao()
    {
        return $this->hasMany(Avaliacao::class);
    }
}
