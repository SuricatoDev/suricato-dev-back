<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'estados';
    protected $fillable = [
        'nome',
        'sigla',
    ];

    /* ************************** RELACIONAMENTOS ************************** */

    public function cidades()
    {
        return $this->hasMany(Cidade::class)->orderBy('nome');
    }
}
