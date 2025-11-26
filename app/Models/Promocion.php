<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'valor_descuento_decimal',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'valor_descuento_decimal' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'estado' => 'boolean',
    ];

    // Relaciones
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'promocion_categorias', 'promocion_id', 'categoria_id')
            ->withPivot('aplica_mayorista', 'aplica_minorista')
            ->withTimestamps();
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'promocion_productos', 'promocion_id', 'producto_id')
            ->withPivot('aplica_mayorista', 'aplica_minorista')
            ->withTimestamps();
    }
}
