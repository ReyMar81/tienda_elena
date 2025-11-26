<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $fillable = [
        'producto_id',
        'url',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
