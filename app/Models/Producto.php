<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'precio_compra',
        'precio_venta',
        'precio_venta_mayorista',
        'stock_actual',
        'stock_minimo',
        'marca',
        'categoria_id',
        'estado',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'precio_venta_mayorista' => 'decimal:2',
        'estado' => 'boolean',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function imagenes()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function promociones()
    {
        return $this->belongsToMany(Promocion::class, 'promocion_productos', 'producto_id', 'promocion_id');
    }

    public function kardex()
    {
        return $this->hasMany(KardexInventario::class);
    }

    public function detallesVenta()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function detallesCarrito()
    {
        return $this->hasMany(CarritoDetalle::class);
    }
}
