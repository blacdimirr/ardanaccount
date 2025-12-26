@extends('layouts.admin')
@section('page-title')
    {{ __('Vista previa asiento por servicio') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Nómina') }}</li>
    <li class="breadcrumb-item"><a href="{{ route('nomina-periodos.index') }}">{{ __('Periodos de nómina') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vista previa asiento por servicio') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5>{{ __('Asiento de nómina por servicio') }}</h5>
                        <small class="text-muted">
                            {{ $periodo->nombre }} ({{ $periodo->fecha_inicio }} - {{ $periodo->fecha_fin }})
                        </small>
                    </div>
                    <a href="{{ route('nomina-periodos.index') }}" class="btn btn-sm btn-secondary">
                        {{ __('Volver') }}
                    </a>
                </div>
                <div class="card-body table-border-style">
                    @if (!empty($resumen['services']))
                        <div class="table-responsive">
                            <table class="table table-striped">
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
                                            <td class="text-end">{{ \Auth::user()->priceFormat($servicio['gastos']) }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($servicio['descuentos']) }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($servicio['neto']) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="fw-bold">
                                        <td>{{ __('Total') }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($resumen['totales']['gastos'] ?? 0) }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($resumen['totales']['descuentos'] ?? 0) }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($resumen['totales']['neto'] ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">{{ __('No hay detalles de nómina para generar el asiento.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
