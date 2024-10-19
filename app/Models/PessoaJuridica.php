<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PessoaJuridica extends Model
{
    use HasFactory;

    protected $table = 'pessoa_juridica';

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
}
