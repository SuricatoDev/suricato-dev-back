<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'veiculos';

    protected $fillable = [
        'placa',
        'marca',
        'tipo',
        'capacidade',
        'motorista',
        'contato_motorista',
        'organizador_id',
        'caravana_id',
    ];


    /***************** RELACIONAMENTOS ***************/

    public function organizador()
    {
        return $this->belongsTo(Organizador::class);
    }

    public function caravana()
    {
        return $this->belongsTo(Caravana::class);
    }

}
