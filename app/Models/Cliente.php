<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cliente';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'CASILLERO',
        'IDUSUARIO',
        'FECHANACIMIENTO'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Obtener el usuario asociado con el cliente.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'IDUSUARIO');
    }

    /**
     * Obtener las direcciones asociadas con este cliente.
     */
    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'IDCLIENTE');
    }

    public function direccionPrincipal(): HasOne
    {
        return $this->hasOne(Direccion::class, 'IDCLIENTE')->where('TIPO', 1);
    }

    public function trackings()
    {
        return $this->hasManyThrough(
            Tracking::class,     // El modelo final al que queremos acceder
            Direccion::class,    // El modelo intermedio
            'IDCLIENTE',        // Foreign key en Direccion que apunta a Cliente
            'IDDIRECCION',      // Foreign key en Tracking que apunta a Direccion
            'id',                // Local key en Cliente
            'id'                 // Local key en Direccion
        );
    }
}
