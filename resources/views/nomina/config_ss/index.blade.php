@extends('layouts.admin')
@section('page-title')
    {{ __('Parámetros de seguridad social') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Nómina') }}</li>
    <li class="breadcrumb-item">{{ __('Parámetros de seguridad social') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Parámetros de seguridad social') }}</h5>
                    <small class="text-muted">
                        {{ __('Configure los porcentajes de aportes para TSS, INFOTEP e IDOPPRIL.') }}
                    </small>
                </div>
                <div class="card-body">
                    {{ Form::model($config, ['route' => ['nomina.config_ss.update'], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-uppercase">{{ __('TSS') }}</h6>
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('tss_empleador', __('Porcentaje empleador'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::number('tss_empleador', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.0001', 'min' => 0]) }}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('tss_empleado', __('Porcentaje empleado'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::number('tss_empleado', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.0001', 'min' => 0]) }}
                        </div>
                        <div class="col-md-12">
                            <h6 class="text-uppercase mt-3">{{ __('INFOTEP') }}</h6>
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('infotep_empleador', __('Porcentaje empleador'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::number('infotep_empleador', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.0001', 'min' => 0]) }}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('infotep_empleado', __('Porcentaje empleado'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::number('infotep_empleado', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.0001', 'min' => 0]) }}
                        </div>
                        <div class="col-md-12">
                            <h6 class="text-uppercase mt-3">{{ __('IDOPPRIL') }}</h6>
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('idoppril_empleador', __('Porcentaje empleador'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::number('idoppril_empleador', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.0001', 'min' => 0]) }}
                        </div>
                        <div class="form-group col-md-6">
                            {{ Form::label('idoppril_empleado', __('Porcentaje empleado'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::number('idoppril_empleado', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.0001', 'min' => 0]) }}
                        </div>
                    </div>
                    <div class="text-end">
                        <input type="submit" value="{{ __('Save') }}" class="btn btn-primary">
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
