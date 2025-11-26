<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService)
    {
    }

    public function index()
    {
        return Inertia::render('Reportes/Index');
    }

    public function show($tipo, Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

        // Validar que el rango no sea mayor a 1 año
        if ($fechaInicio->diffInDays($fechaFin) > 365) {
            return back()->withErrors(['fecha_fin' => 'El rango no puede ser mayor a 1 año']);
        }

        $datos = [];
        $titulo = '';

        switch ($tipo) {
            case 'ventas-fecha':
                $datos = $this->reportService->ventasPorFecha($fechaInicio, $fechaFin);
                $titulo = 'Ventas por Fecha';
                break;

            case 'ventas-metodo':
                $datos = $this->reportService->ventasPorMetodo($fechaInicio, $fechaFin);
                $titulo = 'Ventas por Método de Pago';
                break;

            case 'creditos-estado':
                $datos = $this->reportService->creditosPorEstado($fechaInicio, $fechaFin);
                $titulo = 'Créditos por Estado';
                break;

            case 'productos-vendidos':
                $limite = $request->input('limite', 20);
                $datos = $this->reportService->productosMasVendidosPorFecha($fechaInicio, $fechaFin, $limite);
                $titulo = 'Productos Más Vendidos';
                break;

            case 'clientes-top':
                $limite = $request->input('limite', 10);
                $datos = $this->reportService->clientesTop($fechaInicio, $fechaFin, $limite);
                $titulo = 'Clientes Top';
                break;

            case 'inventario-critico':
                $stockMinimo = $request->input('stock_minimo', 10);
                $datos = $this->reportService->inventarioCritico($stockMinimo);
                $titulo = 'Inventario Crítico';
                break;

            default:
                abort(404, 'Tipo de reporte no válido');
        }

        return Inertia::render('Reportes/Show', [
            'tipo' => $tipo,
            'titulo' => $titulo,
            'datos' => $datos,
            'parametros' => [
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'limite' => $request->input('limite'),
                'stock_minimo' => $request->input('stock_minimo')
            ]
        ]);
    }

    public function pdf($tipo, Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

        if ($fechaInicio->diffInDays($fechaFin) > 365) {
            return back()->withErrors(['fecha_fin' => 'El rango no puede ser mayor a 1 año']);
        }

        $datos = [];
        $titulo = '';
        $vista = '';

        switch ($tipo) {
            case 'ventas-fecha':
                $datos = $this->reportService->ventasPorFecha($fechaInicio, $fechaFin);
                $titulo = 'Ventas por Fecha';
                $vista = 'reports.ventas-fecha';
                break;

            case 'ventas-metodo':
                $datos = $this->reportService->ventasPorMetodo($fechaInicio, $fechaFin);
                $titulo = 'Ventas por Método de Pago';
                $vista = 'reports.ventas-metodo';
                break;

            case 'creditos-estado':
                $datos = $this->reportService->creditosPorEstado($fechaInicio, $fechaFin);
                $titulo = 'Créditos por Estado';
                $vista = 'reports.creditos-estado';
                break;

            case 'productos-vendidos':
                $limite = $request->input('limite', 20);
                $datos = $this->reportService->productosMasVendidosPorFecha($fechaInicio, $fechaFin, $limite);
                $titulo = 'Productos Más Vendidos';
                $vista = 'reports.productos-vendidos';
                break;

            case 'clientes-top':
                $limite = $request->input('limite', 10);
                $datos = $this->reportService->clientesTop($fechaInicio, $fechaFin, $limite);
                $titulo = 'Clientes Top';
                $vista = 'reports.clientes-top';
                break;

            case 'inventario-critico':
                $stockMinimo = $request->input('stock_minimo', 10);
                $datos = $this->reportService->inventarioCritico($stockMinimo);
                $titulo = 'Inventario Crítico';
                $vista = 'reports.inventario-critico';
                break;

            default:
                abort(404, 'Tipo de reporte no válido');
        }

        $pdf = Pdf::loadView($vista, [
            'titulo' => $titulo,
            'datos' => $datos,
            'fechaInicio' => $fechaInicio->format('d/m/Y'),
            'fechaFin' => $fechaFin->format('d/m/Y')
        ])->setPaper('a4', 'portrait');

        $nombreArchivo = str_replace(' ', '-', strtolower($titulo)) . '-' . now()->format('YmdHis') . '.pdf';
        
        return $pdf->download($nombreArchivo);
    }
}
