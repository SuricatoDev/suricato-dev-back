<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suporte extends Model
{
    use HasFactory;

    protected $table = 'suporte';

    protected $fillable = [
        'descricao',
        'status',
        'user_id',
    ];

    /**************** RELACIONAMENTOS *****************/

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
