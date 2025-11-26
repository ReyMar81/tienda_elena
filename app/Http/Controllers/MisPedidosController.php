<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MisPedidosController extends Controller
{
    /**
     * Muestra los pedidos del cliente autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Filtrar por estado si se envía
        $estado = $request->input('estado', 'pendiente');

        $query = Venta::with(['vendedor', 'metodoPago', 'detalles.producto'])
            ->where('user_id', $user->id);

        if ($estado === 'pendiente') {
            $query->where('estado', 'pendiente');
        } elseif ($estado === 'pagado') {
            $query->whereIn('estado', ['pagado', 'completada']);
        }

        $pedidos = $query->orderByDesc('created_at')->paginate(10);

        return Inertia::render('MisPedidos/Index', [
            'pedidos' => $pedidos,
            'filtro' => $estado,
        ]);
    }

    /**
     * Muestra el detalle de un pedido específico
     */
    public function show($id)
    {
        $user = auth()->user();

        $pedido = Venta::with(['vendedor', 'metodoPago', 'detalles.producto', 'credito.cuotas'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return Inertia::render('MisPedidos/Show', [
            'pedido' => $pedido,
        ]);
    }
}
