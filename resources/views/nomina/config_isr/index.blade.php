@extends('layouts.admin')
@section('page-title')
    {{ __('Tramos ISR') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Nómina') }}</li>
    <li class="breadcrumb-item">{{ __('Tramos ISR') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        @can('nomina_config_isr_manage')
            <a href="#" data-url="{{ route('nomina.config_isr.create') }}" data-ajax-popup="true"
                data-title="{{ __('Crear tramo ISR') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>{{ __('Retención fija para honorarios') }}</h5>
                    <small class="text-muted">{{ __('Configure el porcentaje de retención para profesionales contratados.') }}</small>
                </div>
                <div class="card-body">
                    {{ Form::model($honorariosConfig, ['route' => ['nomina.config_isr.honorarios.update'], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
                    <div class="row">
                        <div class="form-group col-md-4">
                            {{ Form::label('retencion_honorarios', __('Retención honorarios (%)'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::number('retencion_honorarios', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => 0, 'max' => 100]) }}
                        </div>
                    </div>
                    <div class="text-end">
                        <input type="submit" value="{{ __('Save') }}" class="btn btn-primary">
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Tramos de ISR') }}</h5>
                    <small class="text-muted">{{ __('Define los rangos de renta y su tasa aplicable.') }}</small>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Desde') }}</th>
                                    <th>{{ __('Hasta') }}</th>
                                    <th>{{ __('Tasa (%)') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tramos as $tramo)
                                    <tr>
                                        <td>{{ number_format($tramo->rango_desde, 2) }}</td>
                                        <td>{{ $tramo->rango_hasta !== null ? number_format($tramo->rango_hasta, 2) : __('En adelante') }}</td>
                                        <td>{{ number_format($tramo->tasa, 2) }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('nomina_config_isr_manage')
                                                    <div class="action-btn me-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-url="{{ route('nomina.config_isr.edit', $tramo->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Editar tramo ISR') }}"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('nomina_config_isr_manage')
                                                    <div class="action-btn">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['nomina.config_isr.destroy', $tramo->id], 'id' => 'delete-form-' . $tramo->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $tramo->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
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
