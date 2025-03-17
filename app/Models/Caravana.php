<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caravana extends Model
{
    use HasFactory;

    protected $table = 'caravanas';

    protected $fillable = [
        'titulo',
        'descricao',
        'categoria',
        'data_partida',
        'data_retorno',
        'origem',
        'destino',
        'numero_vagas',
        'valor',
        'organizador_id',
    ];

    /************* RELACIONAMENTOS *************/

    public function organizador()
    {
        return $this->belongsTo(Organizador::class);
    }

    public function eventos()
    {
        return $this->belongsToMany(Evento::class);
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    public function caravanaPassageiros()
    {
        return $this->hasMany(CaravanaPassageiro::class);
    }

    public function imagens()
    {
        return $this->hasMany(CaravanaImagem::class, 'caravana_id', 'id');
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
