<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
     protected $fillable = ['NOMBRE'];

     public $timestamps = false;

     protected $table = 'proveedor';


}
