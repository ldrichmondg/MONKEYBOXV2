<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoMBox extends Model
{
    protected $fillable = ['DESCRIPCION', 'COLORCLASS', 'ORDEN'];

    protected $table = 'estadombox';

    public $timestamps = false;
}
