<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorito extends Model
{
    protected $table = 'favoritos';

    protected $fillable = [
        'caravana_id',
        'user_id',
    ];

    /********************** RELATIONSHIPS ***********************/

    public function caravana()
    {
        return $this->belongsTo(Caravana::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
