@extends('layouts.admin')
@section('page-title')
    {{ __('Comprobante de pago') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Nómina') }}</li>
    <li class="breadcrumb-item">{{ __('Comprobante de pago') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['method' => 'GET', 'route' => ['nomina.comprobantes.index']]) }}
                    <div class="row align-items-end">
                        <div class="form-group col-md-5">
                            {{ Form::label('nomina_periodo_id', __('Periodo de nómina'), ['class' => 'form-label']) }}
                            {{ Form::select('nomina_periodo_id', $periodos->pluck('nombre', 'id'), request('nomina_periodo_id'), ['class' => 'form-control select']) }}
                        </div>
                        <div class="form-group col-md-5">
                            {{ Form::label('empleado_id', __('Empleado'), ['class' => 'form-label']) }}
                            {{ Form::select('empleado_id', $empleados->mapWithKeys(fn($empleado) => [$empleado->id => $empleado->nombre_completo]), request('empleado_id'), ['class' => 'form-control select']) }}
                        </div>
                        <div class="form-group col-md-2">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Consultar') }}</button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    @if ($periodoSeleccionado && $empleadoSeleccionado)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Comprobante de pago') }}</h5>
                        <div class="small text-muted">
                            {{ $empleadoSeleccionado->nombre_completo }} | {{ $periodoSeleccionado->nombre }}
                            ({{ $periodoSeleccionado->fecha_inicio }} - {{ $periodoSeleccionado->fecha_fin }})
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Concepto') }}</th>
                                        <th>{{ __('Naturaleza') }}</th>
                                        <th>{{ __('Tipo') }}</th>
                                        <th class="text-end">{{ __('Monto') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detallesVisibles as $detalle)
                                        <tr>
                                            <td>{{ $detalle->concepto ? $detalle->concepto->nombre : '' }}</td>
                                            <td>{{ $detalle->concepto ? $detalle->concepto->naturaleza : '' }}</td>
                                            <td>{{ $detalle->concepto ? __(ucfirst($detalle->concepto->tipo)) : '' }}</td>
                                            <td class="text-end">{{ number_format($detalle->monto, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">{{ __('No hay conceptos registrados.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if ($detallesVisibles->count())
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-end">{{ __('ISR retenido') }}</th>
                                            <th class="text-end">{{ number_format($isrTotal, 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-end">{{ __('Total') }}</th>
                                            <th class="text-end">{{ number_format($total, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
