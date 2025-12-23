<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ __('Award') }} #{{ $adjudicacion->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ __('Award Record') }}</h2>
    </div>
    <table>
        <tr>
            <th>{{ __('Process') }}</th>
            <td>#{{ $adjudicacion->procesoCompra?->id }}</td>
        </tr>
        <tr>
            <th>{{ __('Supplier') }}</th>
            <td>{{ $adjudicacion->oferta?->proveedor }}</td>
        </tr>
        <tr>
            <th>{{ __('Award Amount') }}</th>
            <td>{{ \Auth::user()->priceFormat($adjudicacion->monto_adjudicado) }}</td>
        </tr>
        <tr>
            <th>{{ __('Date') }}</th>
            <td>{{ \Carbon\Carbon::parse($adjudicacion->fecha_adjudicacion)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>{{ __('Budget Line') }}</th>
            <td>{{ $adjudicacion->partidaPresupuestaria?->name ?? __('N/A') }}</td>
        </tr>
        <tr>
            <th>{{ __('Status') }}</th>
            <td>{{ $adjudicacion->estado }}</td>
        </tr>
    </table>
</body>
</html>
