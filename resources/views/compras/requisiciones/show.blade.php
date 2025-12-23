@extends('layouts.admin')

@section('page-title')
    {{ __('Requisition Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.requisiciones.index') }}">{{ __('Requisitions') }}</a></li>
    <li class="breadcrumb-item">#{{ $requisicion->id }}</li>
@endsection

@section('action-btn')
    <a href="{{ route('compras.procesos.create') }}" class="btn btn-sm btn-primary">
        {{ __('Start Purchase Process') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Requisition') }} #{{ $requisicion->id }}</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('Requesting Area') }}</dt>
                        <dd class="col-sm-8">{{ $requisicion->area_solicitante }}</dd>
                        <dt class="col-sm-4">{{ __('Date') }}</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($requisicion->fecha_requisicion)->format('d/m/Y') }}</dd>
                        <dt class="col-sm-4">{{ __('Status') }}</dt>
                        <dd class="col-sm-8">{{ $requisicion->estado }}</dd>
                        <dt class="col-sm-4">{{ __('Description') }}</dt>
                        <dd class="col-sm-8">{{ $requisicion->descripcion ?? __('N/A') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('compras.requisiciones.edit', $requisicion->id) }}" class="btn btn-sm btn-primary w-100 mb-2">
                        {{ __('Edit') }}
                    </a>
                    <a href="{{ route('compras.requisiciones.print', $requisicion->id) }}" class="btn btn-sm btn-secondary w-100">
                        {{ __('Print') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
