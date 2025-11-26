<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta {{ $venta->numero_venta }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            margin: 3px 0;
        }
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .invoice-info .left, .invoice-info .right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-info .right {
            text-align: right;
        }
        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .totals table {
            margin: 0;
        }
        .totals th, .totals td {
            border: none;
            border-bottom: 1px solid #ddd;
        }
        .totals .total-final {
            font-size: 16px;
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .qr-section {
            clear: both;
            margin-top: 40px;
            text-align: center;
            padding: 20px;
            border: 1px dashed #ccc;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TIENDA ELENA</h1>
        <p>Boleta de Venta</p>
        <p>NIT: 123456789 | Tel: (591) 12345678</p>
    </div>

    <div class="invoice-info">
        <div class="left">
            <p><strong>Cliente:</strong> {{ $venta->user->name }}</p>
            <p><strong>CI/NIT:</strong> {{ $venta->user->ci ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $venta->user->email }}</p>
        </div>
        <div class="right">
            <p class="invoice-number">{{ $venta->numero_venta }}</p>
            <p><strong>Fecha:</strong> {{ $venta->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Vendedor:</strong> {{ $venta->vendedor->name ?? 'Sistema' }}</p>
            <p><strong>Método:</strong> {{ ucfirst($venta->metodo_pago) }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Cant.</th>
                <th style="width: 45%;">Producto</th>
                <th style="width: 15%;" class="text-right">P. Unit.</th>
                <th style="width: 15%;" class="text-right">Desc.</th>
                <th style="width: 15%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->cantidad }}</td>
                <td>
                    {{ $detalle->producto->nombre }}
                    <br><small style="color: #666;">Código: {{ $detalle->producto->codigo }}</small>
                </td>
                <td class="text-right">Bs. {{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="text-right">{{ $detalle->descuento }}%</td>
                <td class="text-right">Bs. {{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <th>Subtotal:</th>
                <td class="text-right">Bs. {{ number_format($venta->subtotal ?? $venta->total, 2) }}</td>
            </tr>
            <tr>
                <th>Descuento:</th>
                <td class="text-right">Bs. {{ number_format($venta->descuento ?? 0, 2) }}</td>
            </tr>
            <tr class="total-final">
                <th>TOTAL:</th>
                <td class="text-right">Bs. {{ number_format($venta->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="qr-section">
        <p><strong>Código de verificación:</strong></p>
        <p style="font-family: monospace; font-size: 14px; margin: 10px 0;">{{ $qrData['uuid'] }}</p>
        <p style="font-size: 10px; color: #666;">QR simulado - Funcionalidad disponible próximamente</p>
    </div>

    <div class="footer">
        <p>¡Gracias por su compra!</p>
        <p>Este documento es una representación impresa de la boleta electrónica</p>
    </div>
</body>
</html>
