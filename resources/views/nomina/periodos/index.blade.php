@extends('layouts.admin')
@section('page-title')
    {{ __('Periodos de nómina') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Nómina') }}</li>
    <li class="breadcrumb-item">{{ __('Periodos de nómina') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        @can('nomina_periodos_manage')
            <a href="#" data-url="{{ route('nomina-periodos.create') }}" data-ajax-popup="true"
                data-title="{{ __('Crear periodo') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
            <a href="{{ route('nomina.comprobantes.index') }}" class="btn btn-sm btn-secondary ms-2"
                data-bs-toggle="tooltip" title="{{ __('Comprobante de pago') }}">
                <i class="ti ti-receipt"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Nombre') }}</th>
                                    <th>{{ __('Fecha inicio') }}</th>
                                    <th>{{ __('Fecha fin') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th width="12%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($periodos as $periodo)
                                    <tr>
                                        <td>{{ $periodo->nombre }}</td>
                                        <td>{{ $periodo->fecha_inicio }}</td>
                                        <td>{{ $periodo->fecha_fin }}</td>
                                        <td>{{ __(ucfirst($periodo->estado)) }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('nomina_periodos_manage')
                                                    <div class="action-btn me-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-url="{{ route('nomina-periodos.edit', $periodo->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Editar periodo') }}"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('nomina_periodos_manage')
                                                    <div class="action-btn me-2">
                                                        <a href="{{ route('nomina.comprobantes.index', ['nomina_periodo_id' => $periodo->id]) }}"
                                                            class="mx-3 btn btn-sm align-items-center bg-secondary"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Comprobante') }}">
                                                            <i class="ti ti-receipt text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('nomina_periodos_manage')
                                                    <div class="action-btn me-2">
                                                        <a href="{{ route('nomina-periodos.preview-asiento', $periodo->id) }}"
                                                            class="mx-3 btn btn-sm align-items-center bg-primary"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Asiento por servicio') }}">
                                                            <i class="ti ti-list-details text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('nomina_periodos_manage')
                                                    <div class="action-btn">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['nomina-periodos.destroy', $periodo->id], 'id' => 'delete-form-' . $periodo->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $periodo->id }}').submit();">
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
