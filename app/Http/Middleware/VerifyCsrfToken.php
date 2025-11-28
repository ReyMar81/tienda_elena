<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Excluir callbacks públicos de PagoFácil y endpoints de simulación
        'pagofacil/callback',
        'webhook/pagofacil-simulado/*',
        'pagofacil-simulado/*',
        'notificacionesPagoFacil',
    ];
}
