@extends('layouts.admin')

@section('page-title')
    {{ __('Plan Anual de Compras') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Compras') }}</li>
    <li class="breadcrumb-item">{{ __('Plan Anual de Compras') }}</li>
@endsection

@section('action-btn')
    @can('compras_pac_manage')
        <div class="d-flex">
            <a href="{{ route('pac.create') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        </div>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Year') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pacs as $pac)
                                    <tr>
                                        <td class="font-style">{{ $pac->anio }}</td>
                                        <td class="font-style">{{ $pac->descripcion }}</td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn me-2">
                                                    <a href="{{ route('pac.show', Crypt::encrypt($pac->id)) }}" class="mx-3 btn btn-sm align-items-center bg-warning" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn me-2">
                                                    <a href="{{ route('pac.edit', Crypt::encrypt($pac->id)) }}" class="mx-3 btn btn-sm align-items-center bg-info" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['pac.destroy', $pac->id], 'id' => 'delete-form-' . $pac->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{ $pac->id }}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
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
