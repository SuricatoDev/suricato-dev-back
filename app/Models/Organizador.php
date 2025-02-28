<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizador extends Model
{
    use HasFactory;

    protected $table = 'organizadores';

    protected $fillable = [
        'user_id',
        'razao_social',
        'cnpj',
        'inscricao_estadual',
        'inscricao_municipal',
    ];

    /************************* RELACIONAMENTOS ************************ */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function caravanas()
    {
        return $this->hasMany(Caravana::class);
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
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
