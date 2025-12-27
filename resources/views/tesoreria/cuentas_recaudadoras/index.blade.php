@extends('layouts.admin')
@section('page-title')
    {{ __('Collection Accounts') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Treasury') }}</li>
    <li class="breadcrumb-item">{{ __('Collection Accounts') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <a href="{{ route('tesoreria.recaudaciones.index') }}" class="btn btn-sm btn-secondary me-2" data-bs-toggle="tooltip" title="{{ __('Collections') }}">
            <i class="ti ti-receipt"></i>
        </a>
        @can('tesoreria_recaudaciones_manage')
            <a href="#" data-url="{{ route('tesoreria.cuentas-recaudadoras.create') }}" data-ajax-popup="true" data-size="lg"
                data-bs-toggle="tooltip" title="{{ __('Create') }}" data-title="{{ __('Create Collection Account') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Bank') }}</th>
                                    <th>{{ __('Account Number') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cuentas as $cuenta)
                                    <tr class="font-style">
                                        <td>{{ $cuenta->banco }}</td>
                                        <td>{{ $cuenta->numero_cuenta }}</td>
                                        <td>{{ $cuenta->tipo }}</td>
                                        <td>
                                            @if ($cuenta->activo)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-info"
                                                        data-url="{{ route('tesoreria.cuentas-recaudadoras.edit', $cuenta->id) }}"
                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}" data-title="{{ __('Edit Collection Account') }}"
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
