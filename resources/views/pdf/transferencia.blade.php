<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Autorización de Transferencia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    </style>
</head>
<body>
    <h3>Hospital San Lorenzo de Los Mina</h3>
    <p><strong>HSLM-2025</strong></p>
    <p><strong>A:</strong> {{$obj['dirigidoA']}}<br>
    <strong>Director General</strong></p>

    <p><strong>Asunto:</strong> Solicitud autorización de elaboración de transferencia</p>
    <p><strong>Fecha:</strong> {{$obj['fecha_venta']}}</p>

    <p>Muy cordialmente, solicito la autorización para la elaboración de transferencia a nombre de <strong>{{$obj['nombreSRL']}}</strong> por <strong>RD${{$obj['montoTotal']}}</strong> ({{$obj['montoTotalTexto']}}), por concepto de: PAGO DE FACT NCF NO. {{$obj['numero_factura']}}, POR ADQUISICIÓN DE {{$obj['detalle']}}, SEGÚN ORDEN DE COMPRA {{$obj['numero_orden']}}.</p>

    <p>Agradeciendo su atención, le saluda.</p>
    <br><br>
    <p>Atentamente,</p>
    <p><strong>Licdo. Eugenio Rosario Rosario</strong><br>
    Director Administrativo y Financiero<br>
    Hospital San Lorenzo de los Mina</p>
</body>
</html>
