<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingProveedor extends Model
{
    use SoftDeletes;

    protected $table = 'trackingproveedor';

    protected $fillable = [
        'TRACKINGPROVEEDOR',
        'IDPROVEEDOR',
        'IDTRACKING',
    ];

    public function proveedor(): belongsTo
    {
        return $this->belongsTo(Proveedor::class, 'IDPROVEEDOR');
    }

    public function tracking(): belongsTo
    {
        return $this->belongsTo(Tracking::class, 'IDTRACKING');
    }

    public function prealerta(): HasOne
    {
        return $this->hasOne(Prealerta::class, 'IDTRACKINGPROVEEDOR', 'id');
    }
}
