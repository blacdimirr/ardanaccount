@extends('layouts.admin')

@section('page-title')
    {{ __('Contract Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.contratos.index') }}">{{ __('Contracts') }}</a></li>
    <li class="breadcrumb-item">#{{ $contrato->id }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5>{{ __('Contract') }} #{{ $contrato->id }}</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('Award') }}</dt>
                        <dd class="col-sm-8">#{{ $contrato->adjudicacion?->id }}</dd>
                        <dt class="col-sm-4">{{ __('Supplier') }}</dt>
                        <dd class="col-sm-8">{{ $contrato->proveedor }}</dd>
                        <dt class="col-sm-4">{{ __('Contract Amount') }}</dt>
                        <dd class="col-sm-8">{{ \Auth::user()->priceFormat($contrato->monto_contrato) }}</dd>
                        <dt class="col-sm-4">{{ __('Start Date') }}</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y') }}</dd>
                        <dt class="col-sm-4">{{ __('End Date') }}</dt>
                        <dd class="col-sm-8">{{ $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') : __('N/A') }}</dd>
                        <dt class="col-sm-4">{{ __('Budget Line') }}</dt>
                        <dd class="col-sm-8">{{ $contrato->partidaPresupuestaria?->name ?? __('N/A') }}</dd>
                        <dt class="col-sm-4">{{ __('Status') }}</dt>
                        <dd class="col-sm-8">{{ $contrato->estado }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('compras.contratos.edit', $contrato->id) }}" class="btn btn-sm btn-primary w-100 mb-2">
                        {{ __('Edit') }}
                    </a>
                    <a href="{{ route('compras.contratos.print', $contrato->id) }}" class="btn btn-sm btn-secondary w-100">
                        {{ __('Print') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
