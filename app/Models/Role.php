<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * Campos asignables
     */
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación con usuarios (muchos a muchos)
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Relación con items de menú
     */
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Helpers para validación de roles
     */
    public function esPropietario()
    {
        return strcasecmp($this->nombre, 'Propietario') === 0;
    }

    public function esVendedor()
    {
        return strcasecmp($this->nombre, 'Vendedor') === 0;
    }

    public function esCliente()
    {
        return strcasecmp($this->nombre, 'Cliente') === 0;
    }

    /**
     * Scope para búsqueda rápida de rol por nombre (case-insensitive)
     */
    public function scopeNombre($query, $nombre)
    {
        return $query->whereRaw('LOWER(nombre) = ?', [strtolower($nombre)]);
    }
}
