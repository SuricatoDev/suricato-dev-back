<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaravanaPassageiro extends Model
{
    use HasFactory;

    protected $table = 'caravana_passageiros';

    protected $fillable = [
        'data',
        'passageiro_id',
        'caravana_id',
        'status',
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
}
