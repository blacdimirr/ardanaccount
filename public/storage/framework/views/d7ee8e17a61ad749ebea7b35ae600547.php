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
    <p><strong>A:</strong> <?php echo e($obj['dirigidoA']); ?><br>
    <strong>Director General</strong></p>

    <p><strong>Asunto:</strong> Solicitud autorización de elaboración de transferencia</p>
    <p><strong>Fecha:</strong> <?php echo e($obj['fecha_venta']); ?></p>

    <p>Muy cordialmente, solicito la autorización para la elaboración de transferencia a nombre de <strong><?php echo e($obj['nombreSRL']); ?></strong> por <strong>RD$<?php echo e($obj['montoTotal']); ?></strong> (<?php echo e($obj['montoTotalTexto']); ?>), por concepto de: PAGO DE FACT NCF NO. <?php echo e($obj['numero_factura']); ?>, POR ADQUISICIÓN DE <?php echo e($obj['detalle']); ?>, SEGÚN ORDEN DE COMPRA <?php echo e($obj['numero_orden']); ?>.</p>

    <p>Agradeciendo su atención, le saluda.</p>
    <br><br>
    <p>Atentamente,</p>
    <p><strong>Licdo. Eugenio Rosario Rosario</strong><br>
    Director Administrativo y Financiero<br>
    Hospital San Lorenzo de los Mina</p>
</body>
</html>
<?php /**PATH C:\Users\willt\Desktop\Workspace Bladimir\codecanyon-aIPVP9oe-accountgo-accounting-and-billing-tool\main-file\resources\views/pdf/transferencia.blade.php ENDPATH**/ ?>