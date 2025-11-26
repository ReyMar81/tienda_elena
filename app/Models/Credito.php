<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'creditos';

    protected $fillable = [
        'venta_id',
        'user_id',
        'monto_credito',
        'interes',
        'cuotas_total',
        'dias_mora',
        'monto_pagado',
        'monto_pendiente',
        'fecha_otorgamiento',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'monto_credito' => 'decimal:2',
        'interes' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto_pendiente' => 'decimal:2',
        'fecha_otorgamiento' => 'datetime',
        'fecha_vencimiento' => 'datetime',
    ];

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopeVencidos($query)
    {
        return $query->where('estado', 'vencido');
    }

    public function scopeEnMora($query)
    {
        return $query->where('dias_mora', '>', 0);
    }

    // Accesorios
    public function getEstaMoraAttribute()
    {
        return $this->dias_mora > 0;
    }

    public function getPorcentajePagadoAttribute()
    {
        if ($this->monto_credito == 0) return 0;
        return ($this->monto_pagado / $this->monto_credito) * 100;
    }

    public function getCuotasPagadasAttribute()
    {
        return $this->cuotas()->where('estado', 'pagada')->count();
    }

    public function getCuotasPendientesAttribute()
    {
        return $this->cuotas()->where('estado', 'pendiente')->count();
    }

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }
}
