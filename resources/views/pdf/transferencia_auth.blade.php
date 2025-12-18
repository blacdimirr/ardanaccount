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
    <p><strong>{{$obj['fecha_venta']}}</strong></p>
    <p><strong>Transf. #0001</strong> Cuenta Operativa 010-390450-6</p>

    <p><strong>A:</strong> Licda. Milagros Martínez de la Rosa<br>
    Gerencia Financiera o Encargado (A) de Contabilidad</p>

    <p><strong>Asunto:</strong> <span class="titulo">Autorización Transferencia</span></p>

    <p>Por este medio le autorizamos realizar la transferencia<br>
    bancaria a la orden de: <strong>{{$obj['nombreSRL']}}</strong></p>

    <table class="tabla-montos">
        <tr>
            <td><strong>Montos:</strong></td>
            <td>RD${{$obj['montoTotal']}}</td>
        </tr>
        <tr>
            <td><strong>Retención:</strong></td>
            <td>RD${{$obj['retencion']}}</td>
        </tr>
        <tr>
            <td><strong>Monto a pagar:</strong></td>
            <td>RD${{$obj['montoCompleto']}}</td>
        </tr>
    </table>

    <p><strong>Por un monto de RD${{$obj['montoCompleto']}} ({{$obj['montoTotalTexto']}})</strong></p>

    <p><strong>CONCEPTO DE:</strong> PAGO DE FACT NCF NO. {{$obj['numero_factura']}}, POR ADQUISICIÓN DE {{$obj['detalle']}}, SEGÚN ORDEN DE COMPRA {{$obj['numero_orden']}}.</p>

    <br>
    <table class="tabla-montos">
        @foreach($obj['cuentas_afectadas'] as $key => $value)
            <tr>
                <td>{{ $value['chart_account_id'] }}</td>
                <td>{{ \Auth::user()->priceFormat($value['price']) }}</td>
            </tr>
        @endforeach
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
