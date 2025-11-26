<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'cuota_id',
        'metodo_pago_id',
        'monto',
        'recargo_extra',
        'interes_mora_cobrado',
        'fecha',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'recargo_extra' => 'decimal:2',
        'interes_mora_cobrado' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    // Relaciones
    public function cuota()
    {
        return $this->belongsTo(Cuota::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }
}
