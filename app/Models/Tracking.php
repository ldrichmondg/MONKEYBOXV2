<?php

namespace App\Models;

use Diccionarios;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tracking extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracking';

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
        'IDAPI',
        'IDTRACKING',
        'DESCRIPCION',
        'DESDE',
        'HASTA',
        'DESTINO',
        'COURIER',
        'DIASTRANSITO',
        'PESO',
        'IDDIRECCION',
        'IDUSUARIO',
        'FECHAENTREGA',
        'RUTAFACTURA',
        'ESTADOMBOX',
        'OBSERVACIONES',
        'ESTADOSINCRONIZADO'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'PESO' => 'decimal:3',
        'DIASTRANSITO' => 'integer',
        'ENTREGADOCLIENTE' => 'boolean',
        'ENTREGADOCOSTARICA' => 'boolean',
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
        'FECHAENTREGA',
    ];

    /**
     * Obtener la dirección asociada con el tracking.
     */
    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'IDDIRECCION');
    }

    /**
     * Obtener el usuario asociado con el tracking.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'IDUSUARIO');
    }

    public function courrierNombreAId($nombreCourrier)
    {
        $listaCourrier = Diccionarios::getDiccionario('courrier');
        $objCourrier = array_filter($listaCourrier, fn ($obj) => $obj->NOMBRE === $nombreCourrier);

        return empty($objCourrier) ? '' : current($objCourrier)->id;
    }

    public function historialesT(): HasMany
    {
        return $this->hasMany(TrackingHistorial::class, 'IDTRACKING', 'id')->whereNull('deleted_at')->orderBy('updated_at', 'asc');
    }

    public function ColorEstado($cotaRica, $entregado, $transito)
    {
        return match (true) {
            $entregado => 'success',
            $cotaRica => 'danger',
            $transito => 'warning',
            default => '',
        };
    }

    public function DescripcionEstado($cotaRica, $entregado, $transito)
    {

        return match (true) {
            $entregado => 'Ya Entregado',
            $cotaRica => 'En Costa Rica',
            $transito => 'En Tránsito',
            default => '',
        };
    }

    public function UltimoPaisEstado()
    {
        $ultimoEstado = $this->historialesT
            ->where('PAISESTADO', '!=', '')
            ->sortByDesc('FECHA')
            ->first();  // Obtiene el primer elemento después de ordenar

        return $ultimoEstado ? $ultimoEstado->PAISESTADO : ''; // Retorna el valor o un valor por defecto si no se encuentra
    }

    // En App\Models\Tracking.php
    public function UltimoPaisEstadoObj()
    {
        $ultimoEstado = $this->historialesT
            ->where('PAISESTADO', '!=', '')
            ->sortByDesc('FECHA')
            ->first();

        return $ultimoEstado ?: null;
    }

    public function estadoMBox(): BelongsTo
    {
        return $this->belongsTo(EstadoMBox::class, 'ESTADOMBOX', 'DESCRIPCION');
    }

    public function estadoSincronizado(): BelongsTo
    {
        return $this->belongsTo(EstadoMBox::class, 'ESTADOSINCRONIZADO', 'DESCRIPCION');
    }

    public function trackingProveedor(): HasOne
    {
        return $this->hasOne(TrackingProveedor::class, 'IDTRACKING', 'id');
    }

}
