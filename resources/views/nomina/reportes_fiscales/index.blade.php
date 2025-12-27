@extends('layouts.admin')
@section('page-title')
    {{ __('Reportes fiscales') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Nómina') }}</li>
    <li class="breadcrumb-item">{{ __('Reportes fiscales') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('IR-3 mensual') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['method' => 'POST', 'route' => ['nomina.reportes_fiscales.ir3']]) }}
                    <div class="row align-items-end">
                        <div class="form-group col-md-4">
                            {{ Form::label('mes', __('Mes'), ['class' => 'form-label']) }}
                            {{ Form::select('mes', $months, now()->month, ['class' => 'form-control select']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('anio', __('Año'), ['class' => 'form-label']) }}
                            {{ Form::select('anio', $years, now()->year, ['class' => 'form-control select']) }}
                        </div>
                        <div class="form-group col-md-4 d-flex gap-2">
                            <button type="submit" name="formato" value="excel" class="btn btn-primary w-100">
                                {{ __('Generar Excel') }}
                            </button>
                            <button type="submit" name="formato" value="txt" class="btn btn-secondary w-100">
                                {{ __('Generar TXT') }}
                            </button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('IR-4 anual') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['method' => 'POST', 'route' => ['nomina.reportes_fiscales.ir4']]) }}
                    <div class="row align-items-end">
                        <div class="form-group col-md-8">
                            {{ Form::label('anio', __('Año'), ['class' => 'form-label']) }}
                            {{ Form::select('anio', $years, now()->year, ['class' => 'form-control select']) }}
                        </div>
                        <div class="form-group col-md-4 d-flex gap-2">
                            <button type="submit" name="formato" value="excel" class="btn btn-primary w-100">
                                {{ __('Generar Excel') }}
                            </button>
                            <button type="submit" name="formato" value="txt" class="btn btn-secondary w-100">
                                {{ __('Generar TXT') }}
                            </button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
