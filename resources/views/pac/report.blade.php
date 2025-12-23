<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Plan Anual de Compras') }} {{ $pac->anio }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .header p { margin: 4px 0 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background-color: #f5f5f5; text-align: left; }
        .text-right { text-align: right; }
        .totals { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $companyName }}</h2>
        <p>{{ __('Plan Anual de Compras') }} - {{ $pac->anio }}</p>
        @if(!empty($pac->descripcion))
            <p>{{ $pac->descripcion }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Budget Line') }}</th>
                <th>{{ __('Object of Expense') }}</th>
                <th>{{ __('Funding Source') }}</th>
                <th>{{ __('Procedure Type') }}</th>
                <th class="text-right">{{ __('Estimated Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pac->items as $item)
                <tr>
                    <td>{{ $item->descripcion }}</td>
                    <td>{{ $item->partidaPresupuestaria->name ?? '' }}</td>
                    <td>{{ $item->objetoGasto ? $item->objetoGasto->code . ' - ' . $item->objetoGasto->description : '' }}</td>
                    <td>{{ $item->fuenteFinanciamiento ? $item->fuenteFinanciamiento->code . ' - ' . $item->fuenteFinanciamiento->description : '' }}</td>
                    <td>{{ $item->tipo_procedimiento }}</td>
                    <td class="text-right">{{ number_format($item->monto_estimado, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="5">{{ __('Total') }}</td>
                <td class="text-right">{{ number_format($pac->items->sum('monto_estimado'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
