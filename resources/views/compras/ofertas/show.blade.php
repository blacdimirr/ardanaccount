@extends('layouts.admin')

@section('page-title')
    {{ __('Offer Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.ofertas.index') }}">{{ __('Offers') }}</a></li>
    <li class="breadcrumb-item">#{{ $oferta->id }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5>{{ __('Offer') }} #{{ $oferta->id }}</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('Process') }}</dt>
                        <dd class="col-sm-8">#{{ $oferta->procesoCompra?->id }}</dd>
                        <dt class="col-sm-4">{{ __('Supplier') }}</dt>
                        <dd class="col-sm-8">{{ $oferta->proveedor }}</dd>
                        <dt class="col-sm-4">{{ __('Amount') }}</dt>
                        <dd class="col-sm-8">{{ \Auth::user()->priceFormat($oferta->monto_ofertado) }}</dd>
                        <dt class="col-sm-4">{{ __('Date') }}</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($oferta->fecha_oferta)->format('d/m/Y') }}</dd>
                        <dt class="col-sm-4">{{ __('Status') }}</dt>
                        <dd class="col-sm-8">{{ $oferta->estado }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('compras.ofertas.edit', $oferta->id) }}" class="btn btn-sm btn-primary w-100">
                        {{ __('Edit') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
