<?php

namespace App\Models;

use App\Helpers\Diccionarios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingHistorial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'historialtracking';

    protected $primaryKey = 'id';

    protected $fillable = [
        'DESCRIPCION',
        'DESCRIPCIONMODIFICADA',
        'CODIGOPOSTAL',
        'PAISESTADO',
        'OCULTADO',
        'TIPO',
        'IDTRACKING',
        'IDCOURIER',
        'COSTARICA',
        'CLIENTE',
        'PERTENECEESTADO'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function courrier()
    {
        $listaCourrier = Diccionarios::getDiccionario('courrier');
        $objCourrier = array_filter($listaCourrier, fn ($obj) => $obj->id === $this->IDCOURIER);

        return empty($objCourrier) ? '' : current($objCourrier)->NOMBRE;
    }

    /**
     * Obtener el tracking asociado con este historial.
     */
    public function tracking()
    {
        return $this->belongsTo(Tracking::class, 'IDTRACKING');
    }

    public function estadoMBox(): BelongsTo
    {
        return $this->belongsTo(EstadoMBox::class, 'PERTENECEESTADO', 'DESCRIPCION');
    }

}
