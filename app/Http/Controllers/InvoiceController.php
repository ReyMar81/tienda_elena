<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function show($id)
    {
        $venta = Venta::with([
            'user',
            'vendedor',
            'detalles.producto.promociones' => function ($q) use ($id) {
                $venta = Venta::find($id);
                if ($venta) {
                    $q->where('fecha_inicio', '<=', $venta->created_at)
                      ->where('fecha_fin', '>=', $venta->created_at);
                }
            }
        ])->findOrFail($id);

        // Generar UUID para QR simulado
        $qrData = [
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'numero_venta' => $venta->numero_venta,
            'total' => $venta->total,
            'fecha' => $venta->created_at->format('Y-m-d H:i:s')
        ];

        return Inertia::render('Ventas/Show', [
            'venta' => $venta,
            'qrData' => $qrData
        ]);
    }

    public function pdf($id)
    {
        $venta = Venta::with([
            'user',
            'vendedor',
            'detalles.producto'
        ])->findOrFail($id);

        $qrData = [
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'numero_venta' => $venta->numero_venta,
            'total' => $venta->total,
            'fecha' => $venta->created_at->format('Y-m-d H:i:s')
        ];

        $pdf = Pdf::loadView('invoices.pdf', compact('venta', 'qrData'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Boleta-{$venta->numero_venta}.pdf");
    }

    public function ticket($id)
    {
        $venta = Venta::with([
            'user',
            'vendedor',
            'detalles.producto'
        ])->findOrFail($id);

        $qrData = [
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'numero_venta' => $venta->numero_venta,
            'total' => $venta->total,
            'fecha' => $venta->created_at->format('Y-m-d H:i:s')
        ];

        $pdf = Pdf::loadView('invoices.ticket', compact('venta', 'qrData'))
            ->setPaper([0, 0, 226.77, 566.93], 'portrait'); // A6: 80mm x 200mm

        return $pdf->download("Ticket-{$venta->numero_venta}.pdf");
    }
}
