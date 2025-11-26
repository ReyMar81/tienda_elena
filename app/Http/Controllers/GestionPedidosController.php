<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\User;
use App\Models\Producto;
use App\Models\MetodoPago;
use App\Services\PromocionService;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoRequest;
use App\Http\Requests\AccionPedidoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GestionPedidosController extends Controller
{
    protected $promocionService;

    public function __construct(PromocionService $promocionService)
    {
        $this->promocionService = $promocionService;
    }

    /**
     * Muestra lista de pedidos con filtros por origen y estado
     */
    public function index(Request $request)
    {
        $origen = $request->input('origen', 'tienda'); // tienda u online
        $estado = $request->input('estado', 'pendiente');

        $query = Venta::with(['user', 'vendedor', 'metodoPago', 'detalles.producto'])
            ->orderBy('created_at', 'desc');

        // Filtrar por origen
        if (in_array($origen, ['tienda', 'online'])) {
            $query->where('origen', $origen);
        }

        // Filtrar por estado si se especifica
        if (in_array($estado, ['pendiente', 'pagado', 'enviado', 'anulado'])) {
            $query->where('estado', $estado);
        }

        // Si es cliente, solo mostrar sus propios pedidos
        if ($request->user()->role_id === 3) {
            $query->where('user_id', $request->user()->id);
        }

        $pedidos = $query->paginate(20);

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
            'filtro_origen' => $origen,
            'filtro_estado' => $estado,
        ]);
    }
    
    /**
     * CU3: Mostrar formulario para crear pedido manual (tienda física)
     */
    public function create()
    {
        // Cargar clientes activos
        $clientes = User::where('role_id', 3)
            ->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellidos', 'email', 'ci', 'telefono']);
        
        // Cargar productos activos con stock
        $productos = Producto::with(['categoria', 'promociones' => function($q) {
            $q->where('estado', true)
                ->where('fecha_inicio', '<=', now())
                ->where('fecha_fin', '>=', now());
        }])
        ->where('estado', true)
        ->where('stock_actual', '>', 0)
        ->orderBy('nombre')
        ->get();
        
        // Cargar métodos de pago
        $metodosPago = MetodoPago::all();
        
        return Inertia::render('Pedidos/Create', [
            'clientes' => $clientes,
            'productos' => $productos,
            'metodosPago' => $metodosPago,
        ]);
    }
    
    /**
     * CU3: Guardar pedido manual creado en tienda física
     */
    public function store(StorePedidoRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // Calcular totales con descuentos
            $subtotal = 0;
            $descuentoTotal = 0;
            $detallesConPrecio = [];
            
            foreach ($request->detalles as $detalle) {
                $producto = Producto::findOrFail($detalle['producto_id']);
                
                // Verificar stock
                if ($producto->stock_actual < $detalle['cantidad']) {
                    return back()->withErrors([
                        'stock' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock_actual}"
                    ]);
                }
                
                // Calcular descuento por promoción
                $descuentoPorcentaje = $this->promocionService->calcularDescuentoProducto($producto, 'minorista');
                $precioUnitario = $producto->precio_venta;
                $descuentoMonto = ($precioUnitario * $descuentoPorcentaje) / 100;
                $precioFinal = $precioUnitario - $descuentoMonto;
                $subtotalLinea = $precioFinal * $detalle['cantidad'];
                
                $subtotal += $subtotalLinea;
                $descuentoTotal += ($descuentoMonto * $detalle['cantidad']);
                
                $detallesConPrecio[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuentoMonto,
                    'subtotal' => $subtotalLinea,
                    'producto' => $producto,
                ];
            }
            
            // Calcular total desde los detalles
            $total = $subtotal;
            
            // Generar número de pedido/venta
            $ultimaVenta = Venta::latest('id')->first();
            $numeroVenta = 'V-' . str_pad(($ultimaVenta->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);
            
            // Crear pedido (solo total, subtotal y descuento van en detalles)
            $pedido = Venta::create([
                'numero_venta' => $numeroVenta,
                'user_id' => $request->user_id,
                'vendedor_id' => auth()->id(),
                'metodo_pago_id' => $request->metodo_pago_id,
                'tipo_pago' => $request->tipo_pago,
                'total' => $total,
                'estado' => 'pendiente',
            ]);
            
            // Crear detalles y descontar stock
            foreach ($detallesConPrecio as $detalle) {
                VentaDetalle::create([
                    'venta_id' => $pedido->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento' => $detalle['descuento'],
                    'subtotal' => $detalle['subtotal'],
                ]);
                
                // Descontar stock
                $detalle['producto']->decrement('stock_actual', $detalle['cantidad']);
            }
            
            // Si se marca "Confirmar inmediatamente"
            if ($request->confirmar_inmediatamente) {
                $pedido->estado = 'pagado';
                $pedido->save();
                
                // Si es crédito, generar crédito y cuotas
                if ($request->tipo_pago === 'credito') {
                    $numeroCuotas = $request->numero_cuotas ?? 3;
                    $this->crearCredito($pedido, $numeroCuotas);
                }
                
                DB::commit();
                
                return redirect()->route('ventas.index')
                    ->with('success', "Venta {$numeroVenta} creada y confirmada exitosamente.");
            }
            
            DB::commit();
            
            return redirect()->route('pedidos.index')
                ->with('success', "Pedido {$numeroVenta} creado exitosamente. Pendiente de confirmación.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear pedido: ' . $e->getMessage());
        }
    }
    
    /**
     * Muestra detalle de un pedido específico
     */
    public function show($id)
    {
        $pedido = Venta::with(['user', 'vendedor', 'metodoPago', 'detalles.producto.categoria'])
            ->findOrFail($id);
        
        return Inertia::render('Pedidos/Show', [
            'pedido' => $pedido,
        ]);
    }
    
    /**
     * Muestra el formulario para editar un pedido pendiente
     */
    public function edit($id)
    {
        $pedido = Venta::with(['user', 'metodoPago', 'detalles.producto'])
            ->findOrFail($id);
        
        // Solo se pueden editar pedidos pendientes
        if ($pedido->estado !== 'pendiente') {
            return redirect()->route('pedidos.index')
                ->with('error', 'Solo se pueden editar pedidos pendientes.');
        }
        
        $clientes = User::where('role_id', 3)->get();
        $productos = Producto::with(['categoria', 'promociones'])
            ->where('estado', true)
            ->get();
        $metodosPago = MetodoPago::all();
        
        return Inertia::render('Pedidos/Edit', [
            'pedido' => $pedido,
            'clientes' => $clientes,
            'productos' => $productos,
            'metodosPago' => $metodosPago,
        ]);
    }
    
    /**
     * Actualiza un pedido pendiente
     */
    public function update(UpdatePedidoRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $pedido = Venta::with('detalles.producto')->findOrFail($id);
            
            // Solo se pueden editar pedidos pendientes
            if ($pedido->estado !== 'pendiente') {
                return back()->with('error', 'Solo se pueden editar pedidos pendientes.');
            }
            
            // Devolver stock de productos originales
            foreach ($pedido->detalles as $detalle) {
                $detalle->producto->increment('stock_actual', $detalle->cantidad);
            }
            
            // Eliminar detalles antiguos
            $pedido->detalles()->delete();
            
            // Calcular totales con descuentos
            $subtotal = 0;
            $descuentoTotal = 0;
            
            foreach ($request->detalles as $detalle) {
                $producto = Producto::findOrFail($detalle['producto_id']);
                
                // Verificar stock
                if ($producto->stock_actual < $detalle['cantidad']) {
                    DB::rollBack();
                    return back()->withErrors([
                        'stock' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock_actual}"
                    ]);
                }
                
                // Calcular descuento
                $descuentoPorcentaje = $this->promocionService->calcularDescuentoProducto($producto, 'minorista');
                $precioUnitario = $producto->precio_venta;
                $descuentoMonto = ($precioUnitario * $descuentoPorcentaje) / 100;
                $precioFinal = $precioUnitario - $descuentoMonto;
                $subtotalLinea = $precioFinal * $detalle['cantidad'];
                
                $subtotal += $subtotalLinea;
                $descuentoTotal += ($descuentoMonto * $detalle['cantidad']);
                
                // Crear nuevo detalle
                VentaDetalle::create([
                    'venta_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuentoMonto,
                    'subtotal' => $subtotalLinea,
                ]);
                
                // Descontar stock
                $producto->decrement('stock_actual', $detalle['cantidad']);
            }
            
            // Actualizar pedido (solo total, subtotal/descuento van en detalles)
            $pedido->user_id = $request->user_id;
            $pedido->metodo_pago_id = $request->metodo_pago_id;
            $pedido->tipo_pago = $request->tipo_pago;
            $pedido->total = $subtotal;
            $pedido->save();
            
            DB::commit();
            
            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido actualizado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar pedido: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirma o cancela un pedido
     */
    public function accion(AccionPedidoRequest $request, $id)
    {
        $pedido = Venta::findOrFail($id);
        
        if ($request->accion === 'confirmar') {
            // Si es crédito y no se proporcionó numero_cuotas, validar
            if ($pedido->tipo_pago === 'credito' && !$request->numero_cuotas) {
                return back()->withErrors([
                    'numero_cuotas' => 'Debe especificar el número de cuotas para pagos a crédito'
                ]);
            }
            
            $pedido->estado = 'pagado';
            $pedido->vendedor_id = auth()->id();
            $pedido->save();
            
            // Si es crédito, crear crédito con las cuotas del request
            if ($pedido->tipo_pago === 'credito') {
                $numeroCuotas = $request->numero_cuotas;
                $this->crearCredito($pedido, $numeroCuotas);
            }
            
            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido confirmado exitosamente.');
        } 
        elseif ($request->accion === 'cancelar') {
            // Devolver stock
            foreach ($pedido->detalles as $detalle) {
                $detalle->producto->increment('stock_actual', $detalle->cantidad);
            }
            
            $pedido->estado = 'anulado';
            $pedido->save();
            
            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido cancelado exitosamente.');
        }
    }
    
    /**
     * Crea el crédito y las cuotas para una venta a crédito
     */
    private function crearCredito(Venta $venta, int $numeroCuotas = 3)
    {
        $interes = 0; // Sin interés por ahora
        $montoCuota = $venta->total / $numeroCuotas;
        
        // Fechas
        $fechaOtorgamiento = now();
        $fechaVencimientoFinal = now()->addMonths($numeroCuotas);
        
        // Crear el crédito
        $credito = Credito::create([
            'venta_id' => $venta->id,
            'monto_credito' => $venta->total,
            'interes' => $interes,
            'cuotas_total' => $numeroCuotas,
            'dias_mora' => 0,
            'monto_pagado' => 0,
            'monto_pendiente' => $venta->total,
            'fecha_otorgamiento' => $fechaOtorgamiento,
            'fecha_vencimiento' => $fechaVencimientoFinal,
            'estado' => 'pendiente',
        ]);
        
        // Crear las cuotas
        for ($i = 1; $i <= $numeroCuotas; $i++) {
            $fechaVencimiento = now()->addMonths($i);
            
            Cuota::create([
                'credito_id' => $credito->id,
                'numero_cuota' => $i,
                'monto' => $montoCuota,
                'interes_cuota' => 0,
                'dias_mora' => 0,
                'monto_pagado' => 0,
                'monto_pendiente' => $montoCuota,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'pendiente',
            ]);
        }
        
        return $credito;
    }
}
