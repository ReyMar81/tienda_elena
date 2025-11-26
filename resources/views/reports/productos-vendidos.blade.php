<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin-bottom: 5px; }
        .periodo { text-align: center; margin-bottom: 15px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
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
                    <th>Producto</th>
                    <th>Código</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Vendido</th>
                    <th class="text-right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                @php $totalVendido = 0; $totalIngresos = 0; @endphp
                @foreach($datos as $index => $producto)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->codigo }}</td>
                    <td class="text-center">{{ $producto->stock }}</td>
                    <td class="text-center">{{ $producto->total_vendido }}</td>
                    <td class="text-right">Bs. {{ number_format($producto->ingresos, 2) }}</td>
                </tr>
                @php 
                    $totalVendido += $producto->total_vendido;
                    $totalIngresos += $producto->ingresos;
                @endphp
                @endforeach
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td colspan="4">TOTAL</td>
                    <td class="text-center">{{ $totalVendido }}</td>
                    <td class="text-right">Bs. {{ number_format($totalIngresos, 2) }}</td>
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
