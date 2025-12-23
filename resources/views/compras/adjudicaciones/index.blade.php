@extends('layouts.admin')

@section('page-title')
    {{ __('Awards') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Awards') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('compras.adjudicaciones.create') }}" class="btn btn-sm btn-primary">
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
                                    <th>{{ __('Award Amount') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adjudicaciones as $adjudicacion)
                                    <tr>
                                        <td>{{ $adjudicacion->id }}</td>
                                        <td>#{{ $adjudicacion->procesoCompra?->id }}</td>
                                        <td>{{ $adjudicacion->oferta?->proveedor }}</td>
                                        <td>{{ \Auth::user()->priceFormat($adjudicacion->monto_adjudicado) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($adjudicacion->fecha_adjudicacion)->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('compras.adjudicaciones.show', $adjudicacion->id) }}" class="btn btn-sm btn-info">
                                                {{ __('View') }}
                                            </a>
                                            <a href="{{ route('compras.adjudicaciones.edit', $adjudicacion->id) }}" class="btn btn-sm btn-primary">
                                                {{ __('Edit') }}
                                            </a>
                                            <a href="{{ route('compras.adjudicaciones.print', $adjudicacion->id) }}" class="btn btn-sm btn-secondary">
                                                {{ __('Print') }}
                                            </a>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['compras.adjudicaciones.destroy', $adjudicacion->id], 'class' => 'd-inline']) !!}
                                            {{ Form::button(__('Delete'), ['type' => 'submit', 'class' => 'btn btn-sm btn-danger show_confirm']) }}
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No awards found.') }}</td>
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
