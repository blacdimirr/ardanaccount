<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ __('Requisition') }} #{{ $requisicion->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    @include('pdf.partials.header')
    <div class="header">
        <h2>{{ __('Requisition') }} #{{ $requisicion->id }}</h2>
    </div>
    <table>
        <tr>
            <th>{{ __('Requesting Area') }}</th>
            <td>{{ $requisicion->area_solicitante }}</td>
        </tr>
        <tr>
            <th>{{ __('Date') }}</th>
            <td>{{ \Carbon\Carbon::parse($requisicion->fecha_requisicion)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>{{ __('Status') }}</th>
            <td>{{ $requisicion->estado }}</td>
        </tr>
        <tr>
            <th>{{ __('Description') }}</th>
            <td>{{ $requisicion->descripcion ?? __('N/A') }}</td>
        </tr>
    </table>
</body>
</html>
