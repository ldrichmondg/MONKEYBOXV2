<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Imagen extends Model
{
    protected $table = 'imagenes';

    protected $fillable = [
        'RUTA',
        'TIPOIMAGEN',
        'IDTRACKING',
    ];

    public function tracking(): HasOne
    {
        return $this->hasOne(Tracking::class, 'IDTRACKING', 'id');
    }
}
