<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KardexInventario extends Model
{
    use HasFactory;

    protected $table = 'kardex_inventario';

    protected $fillable = [
        'producto_id',
        'tipo',
        'cantidad',
        'referencia',
        'observaciones'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
