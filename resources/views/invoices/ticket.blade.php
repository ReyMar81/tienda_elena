<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $venta->numero_venta }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            line-height: 1.4;
            color: #000;
            padding: 10px;
            width: 80mm;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .header h1 {
            font-size: 14px;
            margin-bottom: 3px;
        }
        .info-line {
            margin: 2px 0;
            display: flex;
            justify-content: space-between;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        table {
            width: 100%;
            margin: 5px 0;
        }
        .item-row {
            margin: 3px 0;
        }
        .item-name {
            font-weight: bold;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 8px;
        }
        .totals {
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        .total-final {
            font-size: 11px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px double #000;
        }
        .qr-box {
            text-align: center;
            margin: 10px 0;
            padding: 8px;
            border: 1px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TIENDA ELENA</h1>
        <div>NIT: 123456789</div>
        <div>Tel: (591) 12345678</div>
    </div>

    <div class="separator"></div>

    <div class="info-line">
        <span>Ticket:</span>
        <span class="bold">{{ $venta->numero_venta }}</span>
    </div>
    <div class="info-line">
        <span>Fecha:</span>
        <span>{{ $venta->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="info-line">
        <span>Cliente:</span>
        <span>{{ $venta->user->name }}</span>
    </div>
    @if($venta->vendedor)
    <div class="info-line">
        <span>Vendedor:</span>
        <span>{{ $venta->vendedor->name }}</span>
    </div>
    @endif
    <div class="info-line">
        <span>Método:</span>
        <span>{{ ucfirst($venta->metodo_pago) }}</span>
    </div>

    <div class="separator"></div>

    @foreach($venta->detalles as $detalle)
    <div class="item-row">
        <div class="item-name">{{ $detalle->producto->nombre }}</div>
        <div class="item-details">
            <span>{{ $detalle->cantidad }} x Bs. {{ number_format($detalle->precio_unitario, 2) }}</span>
            @if($detalle->descuento > 0)
            <span>(-{{ $detalle->descuento }}%)</span>
            @endif
            <span class="bold">Bs. {{ number_format($detalle->subtotal, 2) }}</span>
        </div>
    </div>
    @endforeach

    <div class="totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Bs. {{ number_format($venta->subtotal ?? $venta->total, 2) }}</span>
        </div>
        @if(($venta->descuento ?? 0) > 0)
        <div class="total-row">
            <span>Descuento:</span>
            <span>Bs. {{ number_format($venta->descuento, 2) }}</span>
        </div>
        @endif
        <div class="total-row total-final">
            <span>TOTAL:</span>
            <span>Bs. {{ number_format($venta->total, 2) }}</span>
        </div>
    </div>

    <div class="separator"></div>

    <div class="qr-box">
        <div class="bold">Código de verificación</div>
        <div style="font-size: 7px; margin-top: 5px; word-wrap: break-word;">
            {{ $qrData['uuid'] }}
        </div>
        <div style="font-size: 7px; margin-top: 5px; color: #666;">
            QR simulado
        </div>
    </div>

    <div class="footer">
        <div>¡Gracias por su compra!</div>
        <div>www.tiendaelena.com</div>
        <div style="margin-top: 5px;">{{ $venta->created_at->format('d/m/Y H:i:s') }}</div>
    </div>
</body>
</html>
