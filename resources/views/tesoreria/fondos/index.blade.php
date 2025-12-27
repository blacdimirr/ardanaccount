@extends('layouts.admin')
@section('page-title')
    {{ __('Rotary Funds / Petty Cash') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Treasury') }}</li>
    <li class="breadcrumb-item">{{ __('Rotary Funds / Petty Cash') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <a href="{{ route('report.fondos.movimientos') }}" class="btn btn-sm btn-secondary me-2" data-bs-toggle="tooltip" title="{{ __('Fund Movements Report') }}">
            <i class="ti ti-report"></i>
        </a>
        @can('tesoreria_fondos_manage')
            <a href="#" data-url="{{ route('tesoreria.fondos.create') }}" data-ajax-popup="true" data-size="lg"
                data-bs-toggle="tooltip" title="{{ __('Create') }}" data-title="{{ __('Create Rotary Fund') }}"
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
                                    <th>{{ __('Fund') }}</th>
                                    <th>{{ __('Chart Of Account') }}</th>
                                    <th>{{ __('Initial Amount') }}</th>
                                    <th>{{ __('Available Amount') }}</th>
                                    <th width="15%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fondos as $fondo)
                                    <tr class="font-style">
                                        <td>{{ $fondo->nombre }}</td>
                                        <td>{{ $fondo->cuentaContable ? $fondo->cuentaContable->name : '-' }}</td>
                                        <td>{{ \Auth::user()->priceFormat($fondo->monto_inicial) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($fondo->monto_disponible) }}</td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-success"
                                                        data-url="{{ route('tesoreria.fondos.movimientos.create', $fondo->id) }}"
                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        title="{{ __('Register Movement') }}"
                                                        data-title="{{ __('Register Fund Movement') }}" data-size="md">
                                                        <i class="ti ti-arrow-up-right text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-info"
                                                        data-url="{{ route('tesoreria.fondos.edit', $fondo->id) }}"
                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}" data-title="{{ __('Edit Rotary Fund') }}"
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
