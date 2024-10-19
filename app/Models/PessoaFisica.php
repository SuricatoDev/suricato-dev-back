<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PessoaFisica extends Model
{
    use HasFactory;

    protected $table = 'pessoa_fisica';

    protected $fillable = [
        'user_id',
        'nome',
        'cpf',
        'rg',
        'data_nascimento',
    ];

/************************* RELACIONAMENTOS ************************ */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
