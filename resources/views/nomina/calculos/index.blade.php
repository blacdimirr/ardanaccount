@extends('layouts.admin')
@section('page-title')
    {{ __('Cálculo de nómina') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Nómina') }}</li>
    <li class="breadcrumb-item">{{ __('Cálculo de nómina') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['method' => 'GET', 'route' => ['nomina.calculos.index']]) }}
                    <div class="row align-items-end">
                        <div class="form-group col-md-6">
                            {{ Form::label('nomina_periodo_id', __('Periodo de nómina'), ['class' => 'form-label']) }}
                            {{ Form::select('nomina_periodo_id', $periodos->pluck('nombre', 'id'), request('nomina_periodo_id'), ['class' => 'form-control select']) }}
                        </div>
                        <div class="form-group col-md-3">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Consultar') }}</button>
                        </div>
                        @if ($periodoSeleccionado)
                            <div class="form-group col-md-3">
                                <a href="{{ route('nomina.calculos.export', ['nomina_periodo_id' => $periodoSeleccionado->id]) }}"
                                    class="btn btn-secondary w-100">
                                    {{ __('Exportar aportes') }}
                                </a>
                            </div>
                        @endif
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    @if ($periodoSeleccionado)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5>{{ __('Cálculo de aportes de seguridad social') }}</h5>
                            <small class="text-muted">
                                {{ $periodoSeleccionado->nombre }} ({{ $periodoSeleccionado->fecha_inicio }} - {{ $periodoSeleccionado->fecha_fin }})
                            </small>
                        </div>
                        {{ Form::open(['method' => 'POST', 'route' => ['nomina.calculos.calcular']]) }}
                        {{ Form::hidden('nomina_periodo_id', $periodoSeleccionado->id) }}
                        <button type="submit" class="btn btn-primary">
                            {{ __('Calcular aportes SS') }}
                        </button>
                        {{ Form::close() }}
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Empleado') }}</th>
                                        <th class="text-end">{{ __('Base imponible') }}</th>
                                        <th class="text-end">{{ __('TSS (Empleado)') }}</th>
                                        <th class="text-end">{{ __('INFOTEP (Empleado)') }}</th>
                                        <th class="text-end">{{ __('IDOPPRIL (Empleado)') }}</th>
                                        <th class="text-end">{{ __('TSS (Empleador)') }}</th>
                                        <th class="text-end">{{ __('INFOTEP (Empleador)') }}</th>
                                        <th class="text-end">{{ __('IDOPPRIL (Empleador)') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($calculos as $calculo)
                                        <tr>
                                            <td>{{ $calculo['empleado']->nombre_completo }}</td>
                                            <td class="text-end">{{ number_format($calculo['base_imponible'], 2) }}</td>
                                            <td class="text-end">{{ number_format($calculo['tss_empleado'], 2) }}</td>
                                            <td class="text-end">{{ number_format($calculo['infotep_empleado'], 2) }}</td>
                                            <td class="text-end">{{ number_format($calculo['idoppril_empleado'], 2) }}</td>
                                            <td class="text-end">{{ number_format($calculo['tss_empleador'], 2) }}</td>
                                            <td class="text-end">{{ number_format($calculo['infotep_empleador'], 2) }}</td>
                                            <td class="text-end">{{ number_format($calculo['idoppril_empleador'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">{{ __('No hay empleados disponibles.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
