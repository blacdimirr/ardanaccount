@extends('layouts.admin')

@section('page-title')
    {{ __('Requisitions') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Requisitions') }}</li>
@endsection

@section('action-btn')
    <a href="{{ route('compras.requisiciones.create') }}" class="btn btn-sm btn-primary">
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
                                    <th>{{ __('Requesting Area') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requisiciones as $requisicion)
                                    <tr>
                                        <td>{{ $requisicion->id }}</td>
                                        <td>{{ $requisicion->area_solicitante }}</td>
                                        <td>{{ \Carbon\Carbon::parse($requisicion->fecha_requisicion)->format('d/m/Y') }}</td>
                                        <td>{{ $requisicion->estado }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('compras.requisiciones.show', $requisicion->id) }}" class="btn btn-sm btn-info">
                                                {{ __('View') }}
                                            </a>
                                            <a href="{{ route('compras.requisiciones.edit', $requisicion->id) }}" class="btn btn-sm btn-primary">
                                                {{ __('Edit') }}
                                            </a>
                                            <a href="{{ route('compras.requisiciones.print', $requisicion->id) }}" class="btn btn-sm btn-secondary">
                                                {{ __('Print') }}
                                            </a>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['compras.requisiciones.destroy', $requisicion->id], 'class' => 'd-inline']) !!}
                                            {{ Form::button(__('Delete'), ['type' => 'submit', 'class' => 'btn btn-sm btn-danger show_confirm']) }}
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('No requisitions found.') }}</td>
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
