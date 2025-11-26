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
        .text-center { text-align: center; }
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
                    <th>Cliente</th>
                    <th>Email</th>
                    <th class="text-center">Compras</th>
                    <th class="text-right">Monto Total</th>
                    <th class="text-right">Promedio</th>
                </tr>
            </thead>
            <tbody>
                @php $totalCompras = 0; $totalMonto = 0; @endphp
                @foreach($datos as $index => $cliente)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $cliente->name }}</td>
                    <td>{{ $cliente->email }}</td>
                    <td class="text-center">{{ $cliente->total_compras }}</td>
                    <td class="text-right">Bs. {{ number_format($cliente->monto_total, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($cliente->monto_total / $cliente->total_compras, 2) }}</td>
                </tr>
                @php 
                    $totalCompras += $cliente->total_compras;
                    $totalMonto += $cliente->monto_total;
                @endphp
                @endforeach
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td colspan="3">TOTAL</td>
                    <td class="text-center">{{ $totalCompras }}</td>
                    <td class="text-right">Bs. {{ number_format($totalMonto, 2) }}</td>
                    <td class="text-right">Bs. {{ $totalCompras > 0 ? number_format($totalMonto / $totalCompras, 2) : '0.00' }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px;">No se encontraron datos para el período seleccionado.</p>
    @endif

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
