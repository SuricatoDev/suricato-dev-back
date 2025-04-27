<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    use HasFactory;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'nota',
        'passageiro_id',
        'organizador_id',
        'caravana_id',
        'passageiro',
        'organizador',
    ];


    /**************** RELACIONAMENTOS *****************/

    public function passageiro()
    {
        return $this->belongsTo(Passageiro::class);
    }

    public function caravana()
    {
        return $this->belongsTo(Caravana::class);
    }

    public function organizador()
    {
        return $this->belongsTo(Organizador::class);
    }
}
