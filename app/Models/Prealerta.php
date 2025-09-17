<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prealerta extends Model
{
    use SoftDeletes;

    protected $table = 'prealerta';

    protected $fillable = [
        'DESCRIPCION',
        'VALOR',
        'NOMBRETIENDA',
        'IDCOURIER',
        'IDPREALERTA',
        'IDTRACKINGPROVEEDOR',
    ];

    public function trackingProveedor(): belongsTo
    {
        return $this->belongsTo(TrackingProveedor::class, 'IDTRACKINGPROVEEDOR');
    }
}
