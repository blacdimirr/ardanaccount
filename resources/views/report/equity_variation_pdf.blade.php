<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ __('Equity Variation Statement') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        td.text-end, th.text-end { text-align: right; }
        .section-row { background: #e5e7eb; font-weight: bold; }
        .header { margin-bottom: 12px; }
    </style>
</head>
<body>
    @include('pdf.partials.header')
    <div class="header">
        <h2>{{ __('Equity Variation Statement') }}</h2>
        <p>
            <strong>{{ __('Period') }}:</strong> {{ $startDate }} - {{ $endDate }}
        </p>
        @if (!empty($companyName))
            <p><strong>{{ $companyName }}</strong></p>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>{{ __('Line') }}</th>
                <th class="text-end">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach (['increase', 'decrease'] as $sectionKey)
                @php
                    $section = $report[$sectionKey];
                @endphp
                <tr class="section-row">
                    <td colspan="2">{{ $section['label'] }}</td>
                </tr>
                @foreach ($section['lines'] as $line)
                    <tr>
                        <td>{{ $line['name'] }}</td>
                        <td class="text-end">{{ number_format($line['total'], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th>{{ __('Total') }} {{ $section['label'] }}</th>
                    <th class="text-end">{{ number_format($section['total'], 2) }}</th>
                </tr>
            @endforeach
            <tr>
                <th>{{ __('Net Change in Equity') }}</th>
                <th class="text-end">{{ number_format($report['totals']['net_change'], 2) }}</th>
            </tr>
        </tbody>
    </table>
</body>
</html>
