<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index()
    {
        $user = auth()->user();

        // Dashboard para Cliente
        if ($user->esCliente()) {
            // Obtener productos activos con sus categorías y promociones
            $productos = \App\Models\Producto::with(['categoria', 'imagenes', 'promociones' => function($q) {
                $q->where('fecha_inicio', '<=', now())
                  ->where('fecha_fin', '>=', now());
            }])
            ->where('stock_actual', '>', 0)
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria.nombre');

            // Obtener promociones activas
            $promociones = \App\Models\Promocion::with(['productos' => function($q) {
                $q->where('estado', true)->where('stock_actual', '>', 0);
            }])
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->get();

            // Carrito actual del usuario
            $carrito = \App\Models\Carrito::where('user_id', $user->id)->first();
            $itemsCarrito = $carrito 
                ? \App\Models\CarritoDetalle::where('carrito_id', $carrito->id)
                    ->with('producto')
                    ->get()
                : collect();

            $data = [
                'rol' => 'cliente',
                'productos' => $productos,
                'promociones' => $promociones,
                'carrito' => [
                    'items' => $itemsCarrito,
                    'total' => $itemsCarrito->sum(fn($item) => $item->subtotal),
                    'cantidad_items' => $itemsCarrito->sum('cantidad'),
                ],
                'indicadores' => $this->reportService->indicadoresCliente($user->id),
            ];

            return Inertia::render('Dashboard', $data);
        }

        // Dashboard para Propietario y Vendedor
        $data = [
            'rol' => $user->esPropietario() ? 'propietario' : 'vendedor',
            'kpis' => [
                'ventas_dia' => $this->reportService->ventasDia(),
                'ventas_semana' => $this->reportService->ventasSemana(),
                'ventas_mes' => $this->reportService->ventasMes(),
                'ingresos_dia' => $this->reportService->ingresosTotales('dia'),
                'ingresos_semana' => $this->reportService->ingresosTotales('semana'),
                'ingresos_mes' => $this->reportService->ingresosTotales('mes'),
                'creditos_pendientes' => $this->reportService->creditosPendientes(),
                'creditos_pagados' => $this->reportService->creditosPagados(),
                'cuotas_vencidas' => $this->reportService->cuotasVencidas(),
                'monto_creditos_activos' => $this->reportService->montoCreditos('activo'),
                'total_visitas' => $this->reportService->totalVisitas(),
            ],
            'graficos' => [
                'ventas_por_dia' => $this->reportService->ventasPorDia(7),
                'ventas_por_categoria' => $this->reportService->ventasPorCategoria(5),
                'productos_mas_vendidos' => $this->reportService->productosMasVendidos(10),
                'visitas_por_dia' => $this->reportService->visitasPorDia(7),
                'visitas_top' => $this->reportService->visitasTop(10),
            ],
        ];

        return Inertia::render('Dashboard', $data);
    }
}
