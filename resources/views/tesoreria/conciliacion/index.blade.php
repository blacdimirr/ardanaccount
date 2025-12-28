@extends('layouts.admin')
@section('page-title')
    {{ __('Conciliación bancaria') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Tesorería') }}</li>
    <li class="breadcrumb-item">{{ __('Conciliación bancaria') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <form action="{{ route('tesoreria.conciliacion.auto') }}" method="POST" class="me-2" data-loading="true"
            data-loading-text="{{ __('Conciliando automáticamente...') }}">
            @csrf
            <input type="hidden" name="cuenta_recaudadora_id" value="{{ request('cuenta_recaudadora_id') }}">
            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                title="{{ __('Conciliación automática') }}">
                <i class="ti ti-robot"></i>
            </button>
        </form>
        <a href="{{ route('report.conciliacion.bancaria') }}" class="btn btn-sm btn-secondary" data-loading="true"
            data-loading-text="{{ __('Generando reporte...') }}" data-bs-toggle="tooltip"
            title="{{ __('Reporte de conciliación') }}">
            <i class="ti ti-report"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['tesoreria.conciliacion.index'], 'method' => 'GET']) }}
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            {{ Form::label('cuenta_recaudadora_id', __('Cuenta recaudadora'), ['class' => 'form-label']) }}
                            {{ Form::select('cuenta_recaudadora_id', ['' => __('Todas')] + $cuentaOptions->toArray(), request('cuenta_recaudadora_id'), ['class' => 'form-control select']) }}
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('estado', __('Estado'), ['class' => 'form-label']) }}
                            {{ Form::select('estado', ['' => __('Todos'), 'pendiente' => __('Pendiente'), 'conciliado' => __('Conciliado'), 'parcial' => __('Parcial')], request('estado'), ['class' => 'form-control select']) }}
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-primary mt-4">
                                <i class="ti ti-search"></i> {{ __('Aplicar') }}
                            </button>
                            <a href="{{ route('tesoreria.conciliacion.index') }}" class="btn btn-sm btn-danger mt-4">
                                <i class="ti ti-refresh"></i> {{ __('Restablecer') }}
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Conciliación manual') }}</h5>
                    <small class="text-muted">{{ __('Seleccione un movimiento bancario y concílie con una recaudación interna o un pago a proveedor.') }}</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('tesoreria.conciliacion.manual') }}" method="POST" class="row g-3"
                        data-loading="true" data-loading-text="{{ __('Conciliando movimientos...') }}">
                        @csrf
                        <div class="col-md-4">
                            {{ Form::label('movimiento_id', __('Movimiento bancario'), ['class' => 'form-label']) }}
                            <select name="movimiento_id" class="form-control select" required>
                                <option value="">{{ __('Seleccione') }}</option>
                                @foreach ($movimientos->where('estado_conciliacion', 'pendiente') as $movimiento)
                                    <option value="{{ $movimiento->id }}">
                                        {{ $movimiento->fecha }} - {{ \Auth::user()->priceFormat($movimiento->monto) }} - {{ $movimiento->referencia ?: $movimiento->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('tipo', __('Tipo'), ['class' => 'form-label']) }}
                            {{ Form::select('tipo', ['recaudacion' => __('Recaudación'), 'pago' => __('Pago a proveedor')], null, ['class' => 'form-control select', 'required' => 'required']) }}
                        </div>
                        <div class="col-md-5">
                            {{ Form::label('registro_id', __('Registro interno'), ['class' => 'form-label']) }}
                            <select name="registro_id" class="form-control select" required>
                                <option value="">{{ __('Seleccione') }}</option>
                                <optgroup label="{{ __('Recaudaciones') }}">
                                    @foreach ($recaudaciones as $recaudacion)
                                        <option value="{{ $recaudacion->id }}">
                                            {{ $recaudacion->fecha }} - {{ \Auth::user()->priceFormat($recaudacion->monto) }} - {{ $recaudacion->servicio }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="{{ __('Pagos a proveedores') }}">
                                    @foreach ($pagos as $pago)
                                        <option value="{{ $pago->id }}">
                                            {{ $pago->date }} - {{ \Auth::user()->priceFormat($pago->amount) }} - {{ $pago->reference ?: $pago->description }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-link"></i> {{ __('Conciliar') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Fecha') }}</th>
                                    <th>{{ __('Descripción') }}</th>
                                    <th>{{ __('Referencia') }}</th>
                                    <th>{{ __('Monto') }}</th>
                                    <th>{{ __('Cuenta recaudadora') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th>{{ __('Registro conciliado') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movimientos as $movimiento)
                                    <tr>
                                        <td>{{ \Auth::user()->dateFormat($movimiento->fecha) }}</td>
                                        <td>{{ $movimiento->descripcion ?: '-' }}</td>
                                        <td>{{ $movimiento->referencia ?: '-' }}</td>
                                        <td>{{ \Auth::user()->priceFormat($movimiento->monto) }}</td>
                                        <td>
                                            {{ $movimiento->cuentaRecaudadora ? $movimiento->cuentaRecaudadora->banco . ' - ' . $movimiento->cuentaRecaudadora->numero_cuenta : '-' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $movimiento->estado_conciliacion === 'conciliado' ? 'success' : ($movimiento->estado_conciliacion === 'parcial' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($movimiento->estado_conciliacion) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($movimiento->conciliable)
                                                @if ($movimiento->conciliable_type === \App\Models\Recaudacion::class)
                                                    {{ __('Recaudación') }} #{{ $movimiento->conciliable->id }}
                                                @elseif ($movimiento->conciliable_type === \App\Models\Payment::class)
                                                    {{ __('Pago a proveedor') }} #{{ $movimiento->conciliable->id }}
                                                @else
                                                    -
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
