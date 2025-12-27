@extends('layouts.admin')
@section('page-title')
    {{ __('Collections') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Treasury') }}</li>
    <li class="breadcrumb-item">{{ __('Collections') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <a href="{{ route('report.recaudaciones.diarias') }}" class="btn btn-sm btn-secondary me-2" data-bs-toggle="tooltip" title="{{ __('Daily Collections Report') }}">
            <i class="ti ti-report"></i>
        </a>
        <a href="{{ route('tesoreria.cuentas-recaudadoras.index') }}" class="btn btn-sm btn-secondary me-2" data-bs-toggle="tooltip" title="{{ __('Collection Accounts') }}">
            <i class="ti ti-building-bank"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Quick Collection Entry') }}</h5>
                    <small class="text-muted">{{ __('Register consultations, copays, and private services.') }}</small>
                </div>
                <div class="card-body">
                    @if ($cuentas->isEmpty())
                        <div class="alert alert-warning">
                            {{ __('Please configure a collection account before registering collections.') }}
                            <a href="{{ route('tesoreria.cuentas-recaudadoras.index') }}" class="alert-link">{{ __('Collection Accounts') }}</a>
                        </div>
                    @endif
                    {{ Form::open(['route' => 'tesoreria.recaudaciones.store', 'class' => 'needs-validation', 'novalidate']) }}
                    <div class="row">
                        <div class="form-group col-md-3">
                            {{ Form::label('fecha', __('Date'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::date('fecha', now()->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-3">
                            {{ Form::label('servicio', __('Service'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::select('servicio', $servicios, null, ['class' => 'form-control select', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-3">
                            {{ Form::label('monto', __('Amount'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::number('monto', '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0']) }}
                        </div>
                        <div class="form-group col-md-3">
                            {{ Form::label('metodo_pago', __('Payment Method'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::select('metodo_pago', $metodosPago, null, ['class' => 'form-control select', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('cuenta_recaudadora_id', __('Collection Account'), ['class' => 'form-label']) }}<x-required></x-required>
                            <select name="cuenta_recaudadora_id" class="form-control select" required>
                                @forelse ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">{{ $cuenta->banco }} - {{ $cuenta->numero_cuenta }}</option>
                                @empty
                                    <option value="">{{ __('No accounts available') }}</option>
                                @endforelse
                            </select>
                            <div class="text-xs mt-1">
                                {{ __('Manage accounts here.') }} <a href="{{ route('tesoreria.cuentas-recaudadoras.index') }}"><b>{{ __('Collection Accounts') }}</b></a>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('paciente_id', __('Patient ID (Optional)'), ['class' => 'form-label']) }}
                            {{ Form::number('paciente_id', '', ['class' => 'form-control', 'min' => '1', 'placeholder' => __('Enter patient ID')]) }}
                        </div>
                        <div class="form-group col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-device-floppy me-1"></i>{{ __('Save Collection') }}
                            </button>
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
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Service') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>{{ __('Collection Account') }}</th>
                                    <th>{{ __('Patient') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recaudaciones as $recaudacion)
                                    <tr class="font-style">
                                        <td>{{ \Auth::user()->dateFormat($recaudacion->fecha) }}</td>
                                        <td>{{ $servicios[$recaudacion->servicio] ?? $recaudacion->servicio }}</td>
                                        <td>{{ \Auth::user()->priceFormat($recaudacion->monto) }}</td>
                                        <td>{{ $metodosPago[$recaudacion->metodo_pago] ?? $recaudacion->metodo_pago }}</td>
                                        <td>
                                            {{ $recaudacion->cuentaRecaudadora ? $recaudacion->cuentaRecaudadora->banco . ' - ' . $recaudacion->cuentaRecaudadora->numero_cuenta : '-' }}
                                        </td>
                                        <td>{{ $recaudacion->paciente_id ?: '-' }}</td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-info"
                                                        data-url="{{ route('tesoreria.recaudaciones.edit', $recaudacion->id) }}"
                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}" data-title="{{ __('Edit Collection') }}"
                                                        data-size="lg">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            </span>
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
