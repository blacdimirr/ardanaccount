@extends('layouts.admin')
@section('page-title')
    {{ __('Empleados') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Nómina') }}</li>
    <li class="breadcrumb-item">{{ __('Empleados') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        @can('nomina_empleados_manage')
            <a href="#" data-url="{{ route('nomina-empleados.create') }}" data-ajax-popup="true"
                data-title="{{ __('Crear empleado') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
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
                                    <th>{{ __('Tipo vínculo') }}</th>
                                    <th>{{ __('Unidad/Servicio') }}</th>
                                    <th class="text-end">{{ __('Salario base') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($empleados as $empleado)
                                    <tr>
                                        <td>{{ $empleado->nombre_completo }}</td>
                                        <td>{{ __(ucfirst($empleado->tipo_vinculo)) }}</td>
                                        <td>{{ $empleado->servicioUnidad?->nombre ?? $empleado->unidad_servicio }}</td>
                                        <td class="text-end">{{ number_format($empleado->salario, 2) }}</td>
                                        <td class="Action">
                                            <span>
                                                @can('nomina_empleados_manage')
                                                    <div class="action-btn me-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-url="{{ route('nomina-empleados.edit', $empleado->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Editar empleado') }}"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('nomina_empleados_manage')
                                                    <div class="action-btn">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['nomina-empleados.destroy', $empleado->id], 'id' => 'delete-form-' . $empleado->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $empleado->id }}').submit();">
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
