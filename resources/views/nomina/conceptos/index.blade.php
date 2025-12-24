@extends('layouts.admin')
@section('page-title')
    {{ __('Conceptos de nómina') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Nómina') }}</li>
    <li class="breadcrumb-item">{{ __('Conceptos de nómina') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        @can('nomina_conceptos_manage')
            <a href="#" data-url="{{ route('nomina-conceptos.create') }}" data-ajax-popup="true"
                data-title="{{ __('Crear concepto') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
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
                                    <th>{{ __('Código') }}</th>
                                    <th>{{ __('Nombre') }}</th>
                                    <th>{{ __('Tipo') }}</th>
                                    <th>{{ __('Naturaleza') }}</th>
                                    <th class="text-end">{{ __('Valor/Monto') }}</th>
                                    <th class="text-center">{{ __('Aplica ISR') }}</th>
                                    <th class="text-center">{{ __('Aplica TSS') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($conceptos as $concepto)
                                    <tr>
                                        <td>{{ $concepto->codigo }}</td>
                                        <td>{{ $concepto->nombre }}</td>
                                        <td>{{ __(ucfirst($concepto->tipo)) }}</td>
                                        <td>{{ $concepto->naturaleza }}</td>
                                        <td class="text-end">{{ number_format($concepto->monto ?? 0, 2) }}</td>
                                        <td class="text-center">
                                            @if ($concepto->aplica_isr)
                                                <i class="ti ti-check text-success"></i>
                                            @else
                                                <i class="ti ti-x text-danger"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($concepto->aplica_tss)
                                                <i class="ti ti-check text-success"></i>
                                            @else
                                                <i class="ti ti-x text-danger"></i>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                                @can('nomina_conceptos_manage')
                                                    <div class="action-btn me-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bg-info"
                                                            data-url="{{ route('nomina-conceptos.edit', $concepto->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Editar concepto') }}"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('nomina_conceptos_manage')
                                                    <div class="action-btn">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['nomina-conceptos.destroy', $concepto->id], 'id' => 'delete-form-' . $concepto->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger"
                                                            data-bs-toggle="tooltip" data-original-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $concepto->id }}').submit();">
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
