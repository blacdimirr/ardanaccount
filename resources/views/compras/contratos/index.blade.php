@extends('layouts.admin')

@section('page-title')
    {{ __('Contracts') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Contracts') }}</li>
@endsection

@section('action-btn')
    <a href="{{ route('compras.contratos.create') }}" class="btn btn-sm btn-primary">
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
                                    <th>{{ __('Supplier') }}</th>
                                    <th>{{ __('Award') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contratos as $contrato)
                                    <tr>
                                        <td>{{ $contrato->id }}</td>
                                        <td>{{ $contrato->proveedor }}</td>
                                        <td>#{{ $contrato->adjudicacion?->id }}</td>
                                        <td>{{ \Auth::user()->priceFormat($contrato->monto_contrato) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('compras.contratos.show', $contrato->id) }}" class="btn btn-sm btn-info">
                                                {{ __('View') }}
                                            </a>
                                            <a href="{{ route('compras.contratos.edit', $contrato->id) }}" class="btn btn-sm btn-primary">
                                                {{ __('Edit') }}
                                            </a>
                                            <a href="{{ route('compras.contratos.print', $contrato->id) }}" class="btn btn-sm btn-secondary">
                                                {{ __('Print') }}
                                            </a>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['compras.contratos.destroy', $contrato->id], 'class' => 'd-inline']) !!}
                                            {{ Form::button(__('Delete'), ['type' => 'submit', 'class' => 'btn btn-sm btn-danger show_confirm']) }}
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No contracts found.') }}</td>
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
