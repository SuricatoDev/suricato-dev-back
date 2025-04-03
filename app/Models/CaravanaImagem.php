<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaravanaImagem extends Model
{
    use HasFactory;

    protected $table = 'caravana_imagens';

    protected $fillable = [
        'ordem',
        'path',
        'caravana_id',
    ];


    /***************** RELACIONAMENTOS ******************/

    public function caravana()
    {
        return $this->belongsTo(Caravana::class, 'caravana_id', 'id');
    }
}
