@extends('layouts.admin')
@section('page-title')
    {{ __('Costos de nómina por servicio') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('Costos de nómina por servicio') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('report.nomina.costos.servicio') }}">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('nomina_periodo_id', __('Periodo de nómina'), ['class' => 'form-label']) }}
                                    {{ Form::select('nomina_periodo_id', $periodos->pluck('nombre', 'id'), request('nomina_periodo_id'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Filtrar') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end gap-2 mb-3">
                        <form method="POST" action="{{ route('report.nomina.costos.servicio.export') }}">
                            @csrf
                            <input type="hidden" name="nomina_periodo_id" value="{{ $periodoSeleccionado?->id }}">
                            <button type="submit" class="btn btn-sm btn-success" {{ $periodoSeleccionado ? '' : 'disabled' }}>
                                {{ __('Exportar Excel') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('report.nomina.costos.servicio.pdf') }}">
                            @csrf
                            <input type="hidden" name="nomina_periodo_id" value="{{ $periodoSeleccionado?->id }}">
                            <button type="submit" class="btn btn-sm btn-secondary" {{ $periodoSeleccionado ? '' : 'disabled' }}>
                                {{ __('Exportar PDF') }}
                            </button>
                        </form>
                    </div>
                    @if (!empty($resumen['services']))
                        <div class="table-responsive">
                            <table class="table">
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
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>{{ __('Totales') }}</th>
                                        <th class="text-end">{{ \Auth::user()->priceFormat($resumen['totales']['gastos'] ?? 0) }}</th>
                                        <th class="text-end">{{ \Auth::user()->priceFormat($resumen['totales']['descuentos'] ?? 0) }}</th>
                                        <th class="text-end">{{ \Auth::user()->priceFormat($resumen['totales']['neto'] ?? 0) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ __('Seleccione un periodo para ver el reporte.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
