@extends('layouts.admin')

@section('page-title')
    {{ __('Purchase Process Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.procesos.index') }}">{{ __('Purchase Processes') }}</a></li>
    <li class="breadcrumb-item">#{{ $proceso->id }}</li>
@endsection

@section('action-btn')
    <a href="{{ route('compras.ofertas.create') }}" class="btn btn-sm btn-primary">
        {{ __('Register Offer') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5>{{ __('Process') }} #{{ $proceso->id }}</h5>
                    <p class="mb-1"><strong>{{ __('Requisition') }}:</strong> #{{ $proceso->requisicion?->id }} - {{ $proceso->requisicion?->area_solicitante }}</p>
                    <p class="mb-1"><strong>{{ __('Start Date') }}:</strong> {{ \Carbon\Carbon::parse($proceso->fecha_inicio)->format('d/m/Y') }}</p>
                    <p class="mb-0"><strong>{{ __('Status') }}:</strong> {{ $proceso->estado }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">{{ __('Offers') }}</h6>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Supplier') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($proceso->ofertas as $oferta)
                                    <tr>
                                        <td>{{ $oferta->proveedor }}</td>
                                        <td>{{ \Auth::user()->priceFormat($oferta->monto_ofertado) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($oferta->fecha_oferta)->format('d/m/Y') }}</td>
                                        <td>{{ $oferta->estado }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">{{ __('No offers registered.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($proceso->adjudicacion)
                        <div class="alert alert-info mt-3">
                            {{ __('Award already recorded.') }}
                            <a href="{{ route('compras.adjudicaciones.show', $proceso->adjudicacion->id) }}" class="ms-2">
                                {{ __('View award') }}
                            </a>
                        </div>
                    @else
                        <a href="{{ route('compras.adjudicaciones.create') }}" class="btn btn-sm btn-secondary mt-3">
                            {{ __('Create Award') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
