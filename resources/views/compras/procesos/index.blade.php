@extends('layouts.admin')

@section('page-title')
    {{ __('Purchase Processes') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase Processes') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('compras.procesos.create') }}" class="btn btn-sm btn-primary">
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
                                    <th>{{ __('Requisition') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($procesos as $proceso)
                                    <tr>
                                        <td>{{ $proceso->id }}</td>
                                        <td>#{{ $proceso->requisicion?->id }} - {{ $proceso->requisicion?->area_solicitante }}</td>
                                        <td>{{ \Carbon\Carbon::parse($proceso->fecha_inicio)->format('d/m/Y') }}</td>
                                        <td>{{ $proceso->estado }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('compras.procesos.show', $proceso->id) }}" class="btn btn-sm btn-info">
                                                {{ __('View') }}
                                            </a>
                                            <a href="{{ route('compras.procesos.edit', $proceso->id) }}" class="btn btn-sm btn-primary">
                                                {{ __('Edit') }}
                                            </a>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['compras.procesos.destroy', $proceso->id], 'class' => 'd-inline']) !!}
                                            {{ Form::button(__('Delete'), ['type' => 'submit', 'class' => 'btn btn-sm btn-danger show_confirm']) }}
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('No purchase processes found.') }}</td>
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
