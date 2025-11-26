<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Producto;
use App\Models\PageVisit;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Obtiene ventas del día actual
     */
    public function ventasDia()
    {
        return Venta::whereDate('created_at', today())
            ->where('estado', 'completada')
            ->count();
    }

    /**
     * Obtiene ventas de la última semana
     */
    public function ventasSemana()
    {
        return Venta::whereBetween('created_at', [now()->subWeek(), now()])
            ->where('estado', 'completada')
            ->count();
    }

    /**
     * Obtiene ventas del mes actual
     */
    public function ventasMes()
    {
        return Venta::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('estado', 'completada')
            ->count();
    }

    /**
     * Calcula ingresos totales por período
     */
    public function ingresosTotales($periodo = 'mes')
    {
        $query = Venta::where('estado', 'completada');

        switch ($periodo) {
            case 'dia':
                $query->whereDate('created_at', today());
                break;
            case 'semana':
                $query->whereBetween('created_at', [now()->subWeek(), now()]);
                break;
            case 'mes':
            default:
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
        }

        return $query->sum('total');
    }

    /**
     * Ventas agrupadas por categoría
     */
    public function ventasPorCategoria($limite = 5)
    {
        return DB::table('detalle_venta')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->select('categorias.nombre', DB::raw('SUM(detalle_venta.cantidad) as total'))
            ->groupBy('categorias.nombre')
            ->orderByDesc('total')
            ->limit($limite)
            ->get();
    }

    /**
     * Productos más vendidos
     */
    public function productosMasVendidos($limite = 10)
    {
        return DB::table('detalle_venta')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->select(
                'productos.nombre',
                'productos.codigo',
                DB::raw('SUM(detalle_venta.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_venta.subtotal) as ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByDesc('total_vendido')
            ->limit($limite)
            ->get();
    }

    /**
     * Créditos pendientes
     */
    public function creditosPendientes()
    {
        return Credito::where('estado', 'activo')->count();
    }

    /**
     * Créditos pagados
     */
    public function creditosPagados()
    {
        return Credito::where('estado', 'pagado')->count();
    }

    /**
     * Cuotas vencidas
     */
    public function cuotasVencidas()
    {
        return Cuota::where('estado', 'vencida')->count();
    }

    /**
     * Monto total de créditos activos
     */
    public function montoCreditos($estado = 'activo')
    {
        return Credito::where('estado', $estado)->sum('monto_pendiente');
    }

    /**
     * Páginas más visitadas
     */
    public function visitasTop($limite = 10)
    {
        return PageVisit::orderByDesc('contador')
            ->limit($limite)
            ->get();
    }

    /**
     * Total de visitas registradas
     */
    public function totalVisitas()
    {
        return PageVisit::sum('contador');
    }

    /**
     * Visitas agrupadas por fecha (últimos 7 días)
     */
    public function visitasPorDia($dias = 7)
    {
        return PageVisit::select(
                DB::raw('DATE(updated_at) as fecha'),
                DB::raw('SUM(contador) as total')
            )
            ->whereBetween('updated_at', [now()->subDays($dias), now()])
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();
    }

    /**
     * Indicadores personales del cliente
     */
    public function indicadoresCliente($userId)
    {
        $totalCompras = Venta::where('user_id', $userId)
            ->where('estado', 'completada')
            ->count();

        $totalGastado = Venta::where('user_id', $userId)
            ->where('estado', 'completada')
            ->sum('total');

        $creditosActivos = Credito::whereHas('venta', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('estado', 'activo')->count();

        $deudaPendiente = Credito::whereHas('venta', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('estado', 'activo')->sum('monto_pendiente');

        $cuotasPendientes = Cuota::whereHas('credito.venta', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereIn('estado', ['pendiente', 'vencida'])->count();

        $cuotasVencidas = Cuota::whereHas('credito.venta', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('estado', 'vencida')->count();

        return [
            'total_compras' => $totalCompras,
            'total_gastado' => $totalGastado,
            'creditos_activos' => $creditosActivos,
            'deuda_pendiente' => $deudaPendiente,
            'cuotas_pendientes' => $cuotasPendientes,
            'cuotas_vencidas' => $cuotasVencidas,
        ];
    }

    /**
     * Ventas agrupadas por día (para gráficos)
     */
    public function ventasPorDia($dias = 7)
    {
        return Venta::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as monto')
            )
            ->where('estado', 'completada')
            ->whereBetween('created_at', [now()->subDays($dias), now()])
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();
    }

    /**
     * CU8 - Ventas por rango de fechas
     */
    public function ventasPorFecha($fechaInicio, $fechaFin)
    {
        return Venta::with(['user', 'vendedor', 'detalles.producto'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * CU8 - Ventas agrupadas por método de pago
     */
    public function ventasPorMetodo($fechaInicio, $fechaFin)
    {
        return Venta::select(
                'metodo_pago',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as monto_total')
            )
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('metodo_pago')
            ->orderByDesc('cantidad')
            ->get();
    }

    /**
     * CU8 - Créditos agrupados por estado
     */
    public function creditosPorEstado($fechaInicio, $fechaFin)
    {
        return Credito::with(['venta.user'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->select(
                'estado',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto_total) as monto_total'),
                DB::raw('SUM(monto_pendiente) as monto_pendiente')
            )
            ->groupBy('estado')
            ->get();
    }

    /**
     * CU8 - Productos más vendidos en rango de fechas
     */
    public function productosMasVendidosPorFecha($fechaInicio, $fechaFin, $limite = 20)
    {
        return DB::table('detalle_venta')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->whereBetween('ventas.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'productos.nombre',
                'productos.codigo',
                'productos.stock',
                DB::raw('SUM(detalle_venta.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_venta.subtotal) as ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo', 'productos.stock')
            ->orderByDesc('total_vendido')
            ->limit($limite)
            ->get();
    }

    /**
     * CU8 - Clientes top por compras
     */
    public function clientesTop($fechaInicio, $fechaFin, $limite = 10)
    {
        return DB::table('ventas')
            ->join('users', 'ventas.user_id', '=', 'users.id')
            ->whereBetween('ventas.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'users.name',
                'users.email',
                DB::raw('COUNT(ventas.id) as total_compras'),
                DB::raw('SUM(ventas.total) as monto_total')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('monto_total')
            ->limit($limite)
            ->get();
    }

    /**
     * CU8 - Inventario crítico (stock bajo)
     */
    public function inventarioCritico($stockMinimo = 10)
    {
        return Producto::with('categoria')
            ->where('stock', '<=', $stockMinimo)
            ->where('activo', true)
            ->orderBy('stock', 'asc')
            ->get();
    }
}
