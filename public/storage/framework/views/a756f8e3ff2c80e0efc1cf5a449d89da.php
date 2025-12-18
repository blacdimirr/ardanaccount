<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Autorización de Transferencia</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }
        .logo {
            font-weight: bold;
            color: #1b75bc;
            font-size: 18px;
        }
        .titulo {
            font-weight: bold;
            text-decoration: underline;
        }
        .tabla-montos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .tabla-montos td {
            padding: 4px;
        }
        .firma {
            margin-top: 50px;
            width: 100%;
        }
        .firma td {
            width: 50%;
            vertical-align: top;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="logo">SAN LORENZO DE LOS MINA</div>
    <p><strong><?php echo e($obj['fecha_venta']); ?></strong></p>
    <p><strong>Transf. #0001</strong> Cuenta Operativa 010-390450-6</p>

    <p><strong>A:</strong> Licda. Milagros Martínez de la Rosa<br>
    Gerencia Financiera o Encargado (A) de Contabilidad</p>

    <p><strong>Asunto:</strong> <span class="titulo">Autorización Transferencia</span></p>

    <p>Por este medio le autorizamos realizar la transferencia<br>
    bancaria a la orden de: <strong><?php echo e($obj['nombreSRL']); ?></strong></p>

    <table class="tabla-montos">
        <tr>
            <td><strong>Montos:</strong></td>
            <td>RD$<?php echo e($obj['montoTotal']); ?></td>
        </tr>
        <tr>
            <td><strong>Retención:</strong></td>
            <td>RD$<?php echo e($obj['retencion']); ?></td>
        </tr>
        <tr>
            <td><strong>Monto a pagar:</strong></td>
            <td>RD$<?php echo e($obj['montoCompleto']); ?></td>
        </tr>
    </table>

    <p><strong>Por un monto de RD$<?php echo e($obj['montoCompleto']); ?> (<?php echo e($obj['montoTotalTexto']); ?>)</strong></p>

    <p><strong>CONCEPTO DE:</strong> PAGO DE FACT NCF NO. <?php echo e($obj['numero_factura']); ?>, POR ADQUISICIÓN DE <?php echo e($obj['detalle']); ?>, SEGÚN ORDEN DE COMPRA <?php echo e($obj['numero_orden']); ?>.</p>

    <br>
    <table class="tabla-montos">
        <?php $__currentLoopData = $obj['cuentas_afectadas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($value['chart_account_id']); ?></td>
                <td><?php echo e(\Auth::user()->priceFormat($value['price'])); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>


    <table class="firma">
        <tr>
            <td>
                <br><br>
                ____________________________<br>
                Dr. Armando Carrejo<br>
                Director General
            </td>
            <td>
                <br><br>
                ____________________________<br>
                Licdo. Eugenio Rosario Rosario<br>
                Administrador
            </td>
        </tr>
    </table>

</body>
</html>
<?php /**PATH C:\Users\willt\Desktop\Workspace Bladimir\codecanyon-aIPVP9oe-accountgo-accounting-and-billing-tool\main-file\resources\views/pdf/transferencia_auth.blade.php ENDPATH**/ ?>