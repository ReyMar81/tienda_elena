<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relaciones
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'metodo_pago_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'metodo_pago_id');
    }
}
