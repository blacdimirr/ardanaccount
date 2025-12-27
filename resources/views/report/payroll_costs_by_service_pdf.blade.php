<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ __('Costos de nómina por servicio') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        td.text-end, th.text-end { text-align: right; }
        .header { margin-bottom: 12px; }
    </style>
</head>
<body>
    @include('pdf.partials.header')
    <div class="header">
        <h2>{{ __('Costos de nómina por servicio') }}</h2>
        <p>
            <strong>{{ __('Periodo') }}:</strong> {{ $periodo->nombre }}<br>
            <strong>{{ __('Fecha fin') }}:</strong> {{ $periodo->fecha_fin }}
        </p>
        @if (!empty($settings['company_name']))
            <p><strong>{{ $settings['company_name'] }}</strong></p>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>{{ __('Servicio/Unidad') }}</th>
                <th class="text-end">{{ __('Gastos') }}</th>
                <th class="text-end">{{ __('Descuentos') }}</th>
                <th class="text-end">{{ __('Neto') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resumen['services'] as $servicio)
                <tr>
                    <td>{{ $servicio['servicio'] }}</td>
                    <td class="text-end">{{ number_format($servicio['gastos'], 2) }}</td>
                    <td class="text-end">{{ number_format($servicio['descuentos'], 2) }}</td>
                    <td class="text-end">{{ number_format($servicio['neto'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>{{ __('Totales') }}</th>
                <th class="text-end">{{ number_format($resumen['totales']['gastos'] ?? 0, 2) }}</th>
                <th class="text-end">{{ number_format($resumen['totales']['descuentos'] ?? 0, 2) }}</th>
                <th class="text-end">{{ number_format($resumen['totales']['neto'] ?? 0, 2) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
