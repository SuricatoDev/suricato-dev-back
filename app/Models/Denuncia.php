<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denuncia extends Model
{
    use HasFactory;

    protected $table = 'denuncias';

    protected $fillable = [
        'descricao',
        'status',
        'denunciante_id',
        'passageiro_id',
        'organizador_id',
        'caravana_id',
    ];


    /****************** RELACIONAMENTOS *******************/

    public function denunciante()
    {
        return $this->belongsTo(User::class, 'denunciante_id');
    }

    public function passageiro()
    {
        return $this->belongsTo(Passageiro::class);
    }

    public function organizador()
    {
        return $this->belongsTo(Organizador::class);
    }

    public function caravana()
    {
        return $this->belongsTo(Caravana::class);
    }
}
