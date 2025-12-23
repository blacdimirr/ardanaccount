@extends('layouts.admin')

@section('page-title')
    {{ __('Award Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.adjudicaciones.index') }}">{{ __('Awards') }}</a></li>
    <li class="breadcrumb-item">#{{ $adjudicacion->id }}</li>
@endsection

@section('action-btn')
    <a href="{{ route('compras.contratos.create') }}" class="btn btn-sm btn-primary">
        {{ __('Create Contract') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5>{{ __('Award') }} #{{ $adjudicacion->id }}</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('Process') }}</dt>
                        <dd class="col-sm-8">#{{ $adjudicacion->procesoCompra?->id }}</dd>
                        <dt class="col-sm-4">{{ __('Supplier') }}</dt>
                        <dd class="col-sm-8">{{ $adjudicacion->oferta?->proveedor }}</dd>
                        <dt class="col-sm-4">{{ __('Award Amount') }}</dt>
                        <dd class="col-sm-8">{{ \Auth::user()->priceFormat($adjudicacion->monto_adjudicado) }}</dd>
                        <dt class="col-sm-4">{{ __('Date') }}</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($adjudicacion->fecha_adjudicacion)->format('d/m/Y') }}</dd>
                        <dt class="col-sm-4">{{ __('Budget Line') }}</dt>
                        <dd class="col-sm-8">{{ $adjudicacion->partidaPresupuestaria?->name ?? __('N/A') }}</dd>
                        <dt class="col-sm-4">{{ __('Status') }}</dt>
                        <dd class="col-sm-8">{{ $adjudicacion->estado }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('compras.adjudicaciones.edit', $adjudicacion->id) }}" class="btn btn-sm btn-primary w-100 mb-2">
                        {{ __('Edit') }}
                    </a>
                    <a href="{{ route('compras.adjudicaciones.print', $adjudicacion->id) }}" class="btn btn-sm btn-secondary w-100 mb-2">
                        {{ __('Print') }}
                    </a>
                    @if ($adjudicacion->contrato)
                        <div class="alert alert-info mb-0">
                            {{ __('Contract already created.') }}
                            <a href="{{ route('compras.contratos.show', $adjudicacion->contrato->id) }}" class="ms-2">
                                {{ __('View') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
