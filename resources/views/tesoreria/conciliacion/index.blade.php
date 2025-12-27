@extends('layouts.admin')
@section('page-title')
    {{ __('Bank Reconciliation') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Treasury') }}</li>
    <li class="breadcrumb-item">{{ __('Bank Reconciliation') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <form action="{{ route('tesoreria.conciliacion.auto') }}" method="POST" class="me-2">
            @csrf
            <input type="hidden" name="cuenta_recaudadora_id" value="{{ request('cuenta_recaudadora_id') }}">
            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Auto Reconcile') }}">
                <i class="ti ti-robot"></i>
            </button>
        </form>
        <a href="{{ route('report.conciliacion.bancaria') }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="{{ __('Reconciliation Report') }}">
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
                            {{ Form::label('cuenta_recaudadora_id', __('Collection Account'), ['class' => 'form-label']) }}
                            {{ Form::select('cuenta_recaudadora_id', ['' => __('All')] + $cuentaOptions->toArray(), request('cuenta_recaudadora_id'), ['class' => 'form-control select']) }}
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('estado', __('Status'), ['class' => 'form-label']) }}
                            {{ Form::select('estado', ['' => __('All'), 'pendiente' => __('Pending'), 'conciliado' => __('Reconciled'), 'parcial' => __('Partial')], request('estado'), ['class' => 'form-control select']) }}
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-primary mt-4">
                                <i class="ti ti-search"></i> {{ __('Apply') }}
                            </button>
                            <a href="{{ route('tesoreria.conciliacion.index') }}" class="btn btn-sm btn-danger mt-4">
                                <i class="ti ti-refresh"></i> {{ __('Reset') }}
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
                    <h5>{{ __('Manual Reconciliation') }}</h5>
                    <small class="text-muted">{{ __('Select a bank movement and match it with an internal collection or supplier payment.') }}</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('tesoreria.conciliacion.manual') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            {{ Form::label('movimiento_id', __('Bank Movement'), ['class' => 'form-label']) }}
                            <select name="movimiento_id" class="form-control select" required>
                                <option value="">{{ __('Select') }}</option>
                                @foreach ($movimientos->where('estado_conciliacion', 'pendiente') as $movimiento)
                                    <option value="{{ $movimiento->id }}">
                                        {{ $movimiento->fecha }} - {{ \Auth::user()->priceFormat($movimiento->monto) }} - {{ $movimiento->referencia ?: $movimiento->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('tipo', __('Type'), ['class' => 'form-label']) }}
                            {{ Form::select('tipo', ['recaudacion' => __('Collection'), 'pago' => __('Supplier Payment')], null, ['class' => 'form-control select', 'required' => 'required']) }}
                        </div>
                        <div class="col-md-5">
                            {{ Form::label('registro_id', __('Internal Record'), ['class' => 'form-label']) }}
                            <select name="registro_id" class="form-control select" required>
                                <option value="">{{ __('Select') }}</option>
                                <optgroup label="{{ __('Collections') }}">
                                    @foreach ($recaudaciones as $recaudacion)
                                        <option value="{{ $recaudacion->id }}">
                                            {{ $recaudacion->fecha }} - {{ \Auth::user()->priceFormat($recaudacion->monto) }} - {{ $recaudacion->servicio }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="{{ __('Supplier Payments') }}">
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
                                <i class="ti ti-link"></i> {{ __('Reconcile') }}
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
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Collection Account') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Matched Record') }}</th>
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
                                                    {{ __('Collection') }} #{{ $movimiento->conciliable->id }}
                                                @elseif ($movimiento->conciliable_type === \App\Models\Payment::class)
                                                    {{ __('Supplier Payment') }} #{{ $movimiento->conciliable->id }}
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
