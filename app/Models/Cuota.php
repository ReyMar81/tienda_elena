<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    protected $table = 'cuotas';

    protected $fillable = [
        'credito_id',
        'numero_cuota',
        'monto',
        'interes_cuota',
        'dias_mora',
        'monto_pagado',
        'monto_pendiente',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'interes_cuota' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto_pendiente' => 'decimal:2',
        'fecha_vencimiento' => 'datetime',
    ];

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencida');
    }

    public function scopeProximasVencer($query, $dias = 7)
    {
        return $query->where('estado', 'pendiente')
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)]);
    }

    // Accesorios
    public function getEstaVencidaAttribute()
    {
        return $this->estado === 'pendiente' && $this->fecha_vencimiento < now();
    }

    public function getDiasParaVencimientoAttribute()
    {
        if ($this->estado !== 'pendiente') return null;
        return now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function getPorcentajePagadoAttribute()
    {
        if ($this->monto == 0) return 0;
        return ($this->monto_pagado / $this->monto) * 100;
    }

    // Relaciones
    public function credito()
    {
        return $this->belongsTo(Credito::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
