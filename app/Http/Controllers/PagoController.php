<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrarPagoRequest;
use App\Services\PaymentService;
use App\Services\CreditService;
use App\Services\PasarelaPagoService;
use App\Models\Cuota;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PagoController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected CreditService $creditService,
        protected PasarelaPagoService $pasarelaService
    ) {}

    /**
     * Lista de pagos (Vendedor/Propietario)
     */
    public function index(Request $request)
    {
        $query = \App\Models\Pago::with([
            'cuota.credito.venta.user',
            'metodoPago'
        ])
        ->orderBy('fecha', 'desc');

        // Filtros
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('cuota.credito.venta.user', function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('ci', 'like', "%{$buscar}%");
            });
        }

        $pagos = $query->paginate(15)->withQueryString();

        // Estadísticas
        $totalPagos = \App\Models\Pago::count();
        $totalMonto = \App\Models\Pago::sum('monto');
        $pagosMes = \App\Models\Pago::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'filters' => $request->only(['fecha_desde', 'fecha_hasta', 'buscar']),
            'estadisticas' => [
                'total_pagos' => $totalPagos,
                'total_monto' => $totalMonto,
                'pagos_mes' => $pagosMes,
            ],
        ]);
    }

    /**
     * Ver detalle de un pago
     */
    public function show($id)
    {
        $pago = \App\Models\Pago::with([
            'cuota.credito.venta.user',
            'cuota.credito.venta.detalles.producto',
            'metodoPago'
        ])->findOrFail($id);

        return Inertia::render('Pagos/Show', [
            'pago' => $pago,
        ]);
    }

    /**
     * Formulario para registrar pago manual
     */
    public function create(Request $request)
    {
        $cuotaId = $request->input('cuota_id');
        $cuota = null;
        
        if ($cuotaId) {
            $cuota = Cuota::with(['credito.venta.cliente'])->find($cuotaId);
        }

        $metodosPago = MetodoPago::where('activo', true)->get();

        return Inertia::render('Pagos/Registrar', [
            'cuota' => $cuota,
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Registrar pago manual
     */
    public function store(RegistrarPagoRequest $request)
    {
        try {
            $cuota = Cuota::with('credito')->findOrFail($request->cuota_id);
            $mora = $this->paymentService->calculateLateFee($request->cuota_id);
            $totalDeuda = $cuota->monto_pendiente + $mora;

            if ($request->monto > $totalDeuda) {
                return back()->withErrors(['monto' => 'El monto no puede ser mayor a la deuda total (Bs. ' . number_format($totalDeuda, 2) . ')']);
            }

            $pago = $this->paymentService->registerPayment(
                $request->cuota_id,
                $request->validated()
            );

            return redirect()->route('pagos.index')
                ->with('success', 'Pago registrado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Ver pagos del cliente autenticado
     */
    public function misCreditos()
    {
        $clienteId = auth()->id();
        $creditos = $this->creditService->getCreditsByClient($clienteId);

        return Inertia::render('Pagos/Cliente', [
            'creditos' => $creditos,
        ]);
    }

    /**
     * Generar QR simulado para pago
     */
    public function generarQR(Request $request)
    {
        $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'monto' => 'required|numeric|min:0.01',
        ]);

        $qrData = $this->pasarelaService->generarQR(
            $request->cuota_id,
            $request->monto
        );

        return response()->json($qrData);
    }

    /**
     * Buscar cuota por crédito
     */
    public function buscarCuotas(Request $request)
    {
        $creditoId = $request->input('credito_id');
        
        $cuotas = Cuota::with(['credito.venta.cliente'])
            ->where('credito_id', $creditoId)
            ->whereIn('estado', ['pendiente', 'vencida'])
            ->orderBy('numero_cuota', 'asc')
            ->get()
            ->map(function ($cuota) {
                $mora = $this->paymentService->calculateLateFee($cuota->id);
                $cuota->mora_calculada = $mora;
                $cuota->total_pagar = $cuota->monto_pendiente + $mora;
                return $cuota;
            });

        return response()->json($cuotas);
    }

    /**
     * Historial de pagos de una cuota
     */
    public function historialCuota($cuotaId)
    {
        $cuota = Cuota::with(['pagos.metodoPago', 'credito.venta.cliente'])->findOrFail($cuotaId);

        return response()->json($cuota);
    }
}
