@extends('layouts.admin')
@section('page-title')
    {{ __('Bank Statement Import') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Treasury') }}</li>
    <li class="breadcrumb-item">{{ __('Bank Statement Import') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Import Bank Statement') }}</h5>
                    <small class="text-muted">{{ __('Upload CSV or OFX files to import bank movements.') }}</small>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => 'tesoreria.extractos.preview', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ Form::label('cuenta_recaudadora_id', __('Collection Account'), ['class' => 'form-label']) }}<x-required></x-required>
                            <select name="cuenta_recaudadora_id" class="form-control select" required>
                                <option value="">{{ __('Select an account') }}</option>
                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}" @if (!empty($preview) && (int) $preview['cuenta_recaudadora_id'] === $cuenta->id) selected @endif>
                                        {{ $cuenta->banco }} - {{ $cuenta->numero_cuenta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('archivo', __('Statement File (CSV/OFX)'), ['class' => 'form-label']) }}<x-required></x-required>
                            <input type="file" class="form-control" name="archivo" id="archivo" required>
                        </div>
                        <div class="form-group col-md-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>{{ __('Preview Statement') }}
                            </button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    @if (!empty($preview))
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Statement Preview') }}</h5>
                        <small class="text-muted">
                            {{ __('File') }}: {{ $preview['origen_archivo'] }} ·
                            {{ __('Movements detected') }}: {{ count($preview['movimientos']) }}
                        </small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Reference') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (collect($preview['movimientos'])->take(20) as $movimiento)
                                        <tr>
                                            <td>{{ \Auth::user()->dateFormat($movimiento['fecha']) }}</td>
                                            <td>{{ \Auth::user()->priceFormat($movimiento['monto']) }}</td>
                                            <td>{{ $movimiento['descripcion'] ?: '-' }}</td>
                                            <td>{{ $movimiento['referencia'] ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">{{ __('Showing first 20 movements for preview.') }}</small>
                            {{ Form::open(['route' => 'tesoreria.extractos.confirm', 'method' => 'post']) }}
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check me-1"></i>{{ __('Confirm Import') }}
                            </button>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Imported Bank Movements') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Collection Account') }}</th>
                                    <th>{{ __('Source File') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movimientos as $movimiento)
                                    <tr class="font-style">
                                        <td>{{ \Auth::user()->dateFormat($movimiento->fecha) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($movimiento->monto) }}</td>
                                        <td>{{ $movimiento->descripcion ?: '-' }}</td>
                                        <td>{{ $movimiento->referencia ?: '-' }}</td>
                                        <td>
                                            {{ $movimiento->cuentaRecaudadora ? $movimiento->cuentaRecaudadora->banco . ' - ' . $movimiento->cuentaRecaudadora->numero_cuenta : '-' }}
                                        </td>
                                        <td>{{ $movimiento->origen_archivo }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($movimientos->isEmpty())
                            <p class="text-muted mb-0">{{ __('No bank movements imported yet.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
