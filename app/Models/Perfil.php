<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perfil extends Model
{
    use HasFactory;
    // Desactiva los timestamps
    public $timestamps = false;
    protected $table = 'perfil';

    protected $fillable = ['DESCRIPCION'];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'IDPERFIL');
    }

}
