<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoMBox extends Model
{
    protected $fillable = ['DESCRIPCION', 'COLORCLASS'];

    protected $table = 'estadombox';

    public $timestamps = false;
}
