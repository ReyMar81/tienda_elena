<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin-bottom: 5px; }
        .periodo { text-align: center; margin-bottom: 15px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .totales { margin-top: 15px; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>TIENDA ELENA</h1>
        <p>{{ $titulo }}</p>
    </div>

    <div class="periodo">
        Período: {{ $fechaInicio }} - {{ $fechaFin }}
    </div>

    @if(count($datos) > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Método</th>
                    <th class="text-right">Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @php $totalGeneral = 0; @endphp
                @foreach($datos as $index => $venta)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $venta->numero_venta ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->user->name ?? 'N/A' }}</td>
                    <td>{{ $venta->vendedor->name ?? 'Sistema' }}</td>
                    <td>{{ ucfirst($venta->metodo_pago) }}</td>
                    <td class="text-right">Bs. {{ number_format($venta->total, 2) }}</td>
                    <td>{{ ucfirst($venta->estado) }}</td>
                </tr>
                @php $totalGeneral += $venta->total; @endphp
                @endforeach
            </tbody>
        </table>

        <div class="totales text-right">
            Total General: Bs. {{ number_format($totalGeneral, 2) }}
        </div>
    @else
        <p style="text-align: center; padding: 20px;">No se encontraron datos para el período seleccionado.</p>
    @endif

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
