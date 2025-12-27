@extends('layouts.admin')
@section('page-title')
    {{ __('Importación de extractos bancarios') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Tesorería') }}</li>
    <li class="breadcrumb-item">{{ __('Importación de extractos bancarios') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5>{{ __('Importar extracto bancario') }}</h5>
                            <small class="text-muted">{{ __('Cargue archivos CSV u OFX para importar movimientos bancarios.') }}</small>
                        </div>
                        <a href="{{ route('tesoreria.extractos.template') }}" class="btn btn-sm btn-secondary">
                            <i class="ti ti-download me-1"></i>{{ __('Descargar plantilla CSV') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => 'tesoreria.extractos.preview', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate', 'data-loading' => true, 'data-loading-text' => __('Analizando extracto...')]) }}
                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ Form::label('cuenta_recaudadora_id', __('Cuenta recaudadora'), ['class' => 'form-label']) }}<x-required></x-required>
                            <select name="cuenta_recaudadora_id" class="form-control select" required>
                                <option value="">{{ __('Seleccione una cuenta') }}</option>
                                @foreach ($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}" @if (!empty($preview) && (int) $preview['cuenta_recaudadora_id'] === $cuenta->id) selected @endif>
                                        {{ $cuenta->banco }} - {{ $cuenta->numero_cuenta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('archivo', __('Archivo de extracto (CSV/OFX)'), ['class' => 'form-label']) }}<x-required></x-required>
                            <input type="file" class="form-control" name="archivo" id="archivo" required>
                        </div>
                        <div class="form-group col-md-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>{{ __('Previsualizar extracto') }}
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
                        <h5>{{ __('Vista previa del extracto') }}</h5>
                        <small class="text-muted">
                            {{ __('Archivo') }}: {{ $preview['origen_archivo'] }} ·
                            {{ __('Movimientos detectados') }}: {{ count($preview['movimientos']) }}
                        </small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Fecha') }}</th>
                                        <th>{{ __('Monto') }}</th>
                                        <th>{{ __('Descripción') }}</th>
                                        <th>{{ __('Referencia') }}</th>
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
                            <small class="text-muted">{{ __('Mostrando los primeros 20 movimientos como vista previa.') }}</small>
                            {{ Form::open(['route' => 'tesoreria.extractos.confirm', 'method' => 'post', 'data-loading' => true, 'data-loading-text' => __('Confirmando importación...')]) }}
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check me-1"></i>{{ __('Confirmar importación') }}
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
                    <h5>{{ __('Movimientos bancarios importados') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Fecha') }}</th>
                                    <th>{{ __('Monto') }}</th>
                                    <th>{{ __('Descripción') }}</th>
                                    <th>{{ __('Referencia') }}</th>
                                    <th>{{ __('Cuenta recaudadora') }}</th>
                                    <th>{{ __('Archivo de origen') }}</th>
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
                            <p class="text-muted mb-0">{{ __('No hay movimientos bancarios importados.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
