<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MenuItem Model - Menú Dinámico 3 Capas
 * 
 * Representa un Módulo en la arquitectura de clase:
 * - Persona → Grupo → Módulo → Acciones
 * - User → Role → MenuItem → Rutas
 * 
 * Cada MenuItem pertenece a un Role y define:
 * - etiqueta: Texto visible en menú
 * - ruta_laravel: Nombre de la ruta (ej: 'dashboard', 'creditos.index')
 * - icono: Clase CSS de Bootstrap Icons (ej: 'bi-house-door-fill')
 * - orden: Posición en el menú
 */
class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items';

    protected $fillable = [
        'etiqueta',
        'ruta_laravel',
        'icono',
        'orden',
        'role_id',
        'parent_id',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    // Relaciones
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('orden');
    }
}
