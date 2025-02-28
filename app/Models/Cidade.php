<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'cidades';

    protected $fillable = [
        'nome',
        'estado_id',
    ];

    /* ************************** RELACIONAMENTOS ************************** */

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

}
