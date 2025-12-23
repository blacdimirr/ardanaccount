<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ __('Contract') }} #{{ $contrato->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ __('Contract') }}</h2>
    </div>
    <table>
        <tr>
            <th>{{ __('Award') }}</th>
            <td>#{{ $contrato->adjudicacion?->id }}</td>
        </tr>
        <tr>
            <th>{{ __('Supplier') }}</th>
            <td>{{ $contrato->proveedor }}</td>
        </tr>
        <tr>
            <th>{{ __('Contract Amount') }}</th>
            <td>{{ \Auth::user()->priceFormat($contrato->monto_contrato) }}</td>
        </tr>
        <tr>
            <th>{{ __('Start Date') }}</th>
            <td>{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>{{ __('End Date') }}</th>
            <td>{{ $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') : __('N/A') }}</td>
        </tr>
        <tr>
            <th>{{ __('Budget Line') }}</th>
            <td>{{ $contrato->partidaPresupuestaria?->name ?? __('N/A') }}</td>
        </tr>
        <tr>
            <th>{{ __('Status') }}</th>
            <td>{{ $contrato->estado }}</td>
        </tr>
    </table>
</body>
</html>
