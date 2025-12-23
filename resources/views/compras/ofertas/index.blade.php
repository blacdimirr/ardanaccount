@extends('layouts.admin')

@section('page-title')
    {{ __('Offers') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Offers') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('compras.ofertas.create') }}" class="btn btn-sm btn-primary">
        {{ __('Create') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Process') }}</th>
                                    <th>{{ __('Supplier') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ofertas as $oferta)
                                    <tr>
                                        <td>{{ $oferta->id }}</td>
                                        <td>#{{ $oferta->procesoCompra?->id }}</td>
                                        <td>{{ $oferta->proveedor }}</td>
                                        <td>{{ \Auth::user()->priceFormat($oferta->monto_ofertado) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($oferta->fecha_oferta)->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('compras.ofertas.show', $oferta->id) }}" class="btn btn-sm btn-info">
                                                {{ __('View') }}
                                            </a>
                                            <a href="{{ route('compras.ofertas.edit', $oferta->id) }}" class="btn btn-sm btn-primary">
                                                {{ __('Edit') }}
                                            </a>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['compras.ofertas.destroy', $oferta->id], 'class' => 'd-inline']) !!}
                                            {{ Form::button(__('Delete'), ['type' => 'submit', 'class' => 'btn btn-sm btn-danger show_confirm']) }}
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No offers found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
