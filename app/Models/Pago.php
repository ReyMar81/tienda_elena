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
        'cuota_credito_id',
        'metodo_pago_id',
        'monto',
        'recargo_extra',
        'interes_mora_cobrado',
        'fecha',
        'pago_facil_transaction_id',
        'pago_facil_qr_image',
        'pago_facil_status',
        'pago_facil_raw_response'
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

    public function cuotaCredito()
    {
        return $this->belongsTo(CuotaCredito::class, 'cuota_credito_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }
}
