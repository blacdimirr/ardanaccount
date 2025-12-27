@php
    use App\Models\Utility;
    $settings = $settings ?? Utility::settings();
    $logoBase = Utility::get_file('uploads/logo/');
    $logoFile = $settings['company_logo_dark'] ?? Utility::getValByName('company_logo_dark');
    $logoFile = $logoFile ?: 'logo-dark.png';
    $logoUrl = $logoBase ? $logoBase . $logoFile : '';
    $companyName = $settings['company_name'] ?? config('app.name');
    $addressParts = array_filter([
        $settings['company_address'] ?? '',
        $settings['company_city'] ?? '',
        $settings['company_state'] ?? '',
        $settings['company_zipcode'] ?? '',
        $settings['company_country'] ?? '',
    ]);
    $address = implode(', ', $addressParts);
    $telephone = $settings['company_telephone'] ?? '';
    $rnc = $settings['registration_number'] ?? '';
    $userName = auth()->user()->name ?? 'Sistema';
    $generatedAt = now()->format('d/m/Y H:i');
@endphp

<table style="width: 100%; border-bottom: 1px solid #dcdcdc; margin-bottom: 12px; font-size: 12px;">
    <tr>
        <td style="width: 20%; vertical-align: top;">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" style="max-height: 60px;">
            @endif
        </td>
        <td style="width: 80%; text-align: right; vertical-align: top;">
            <div style="font-size: 14px; font-weight: bold;">{{ $companyName }}</div>
            @if($address)
                <div>{{ $address }}</div>
            @endif
            @if($telephone)
                <div>Teléfono: {{ $telephone }}</div>
            @endif
            @if($rnc)
                <div>RNC: {{ $rnc }}</div>
            @endif
            <div>Generado por: {{ $userName }} | Fecha: {{ $generatedAt }}</div>
        </td>
    </tr>
</table>
