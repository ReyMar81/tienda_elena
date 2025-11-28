<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasProfilePhoto, TwoFactorAuthenticatable;

    /**
     * Campos asignables en masiva.
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'ci',
        'telefono',
        'email',
        'password',
        'role_id',
        'estado',
        'fecha_nacimiento',
    ];

    /**
     * Campos ocultos.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Atributos agregados al array/JSON.
     */
    protected $appends = [
        'name',
        'profile_photo_url',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'estado' => 'boolean',
        'fecha_nacimiento' => 'date',
    ];

    /**
     * Relaciones
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function ventasComoVendedor()
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }

    public function carrito()
    {
        return $this->hasOne(Carrito::class);
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class);
    }

    /**
     * Helpers de rol
     */
    public function tieneRol($rol)
    {
        return $this->role && strcasecmp($this->role->nombre, $rol) === 0;
    }

    public function esPropietario()
    {
        return $this->tieneRol('Propietario');
    }

    public function esVendedor()
    {
        return $this->tieneRol('Vendedor');
    }

    public function esCliente()
    {
        return $this->tieneRol('Cliente');
    }

    /**
     * Compatibilidad: algunos controladores usan esAdministrador() para
     * verificar permisos elevados. Interpretamos "administrador" como
     * cualquier usuario con rol Propietario o Vendedor.
     */
    public function esAdministrador()
    {
        return $this->esPropietario() || $this->esVendedor();
    }

    /**
     * Mutators para formateo automático
     */
    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn($value) => ucfirst(strtolower($value)),
        );
    }

    protected function apellidos(): Attribute
    {
        return Attribute::make(
            set: fn($value) => ucwords(strtolower($value)),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtolower($value),
        );
    }

    /**
     * Accessor para nombre completo
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => trim($this->nombre . ' ' . $this->apellidos),
        );
    }
}