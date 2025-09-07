<?php

namespace App\Models;

use Diccionarios;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Direccion extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'direccion';

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
        'DIRECCION',
        'TIPO',
        'CODIGOPOSTAL',
        'IDCLIENTE',
        'PAISESTADO',
        'LINKWAZE',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'TIPO' => 'integer', // Cast para el campo TIPO
    ];

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
     * Obtener el cliente asociado con la dirección.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'IDCLIENTE');
    }

    /**
     * Obtener los trackings asociados con esta dirección.
     */
    public function trackings()
    {
        return $this->hasMany(Tracking::class, 'IDDIRECCION');
    }

    public function tipoTexto()
    {
        $tiposDireccion = Diccionarios::getDiccionario('tiposDirecciones');
        $objTipo = array_filter($tiposDireccion, fn ($obj) => $obj->id === $this->TIPO);
        $objTipo = array_values($objTipo); // Reindexa para tener acceso con [0]

        return $objTipo[0]->NOMBRE;

    }

    public function direccionCompleta(){
        return $this->PAISESTADO. ', '. $this->DIRECCION;
    }
}
