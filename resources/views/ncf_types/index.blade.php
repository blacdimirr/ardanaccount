@extends('layouts.admin')

@section('page-title')
    {{ __('Tipos de NCF') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Configuración') }}</li>
    <li class="breadcrumb-item">{{ __('Tipos de NCF') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <a href="#" data-url="{{ route('ncf-types.create') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Nuevo tipo de NCF') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Nuevo tipo de NCF') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Código') }}</th>
                                    <th>{{ __('Descripción') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th>{{ __('Actualizado') }}</th>
                                    <th class="text-end">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($types as $type)
                                    <tr>
                                        <td>{{ $type->code }}</td>
                                        <td>{{ $type->description }}</td>
                                        <td>
                                            @if($type->active)
                                                <span class="badge bg-success">{{ __('Activo') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactivo') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $type->updated_at?->format('Y-m-d H:i') }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="#" class="btn btn-sm btn-info me-2" data-url="{{ route('ncf-types.edit', $type->id) }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Editar tipo de NCF') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                @if($type->active)
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['ncf-types.destroy', $type->id], 'id' => 'delete-form-'.$type->id]) !!}
                                                        <a href="#" class="btn btn-sm btn-danger bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Desactivar') }}" data-confirm="{{ __('¿Desactivar tipo de NCF?').'|'.__('Podrás reactivarlo editando la configuración.') }}" data-confirm-yes="document.getElementById('delete-form-{{$type->id}}').submit();">
                                                            <i class="ti ti-power text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">{{ __('Aún no hay tipos de NCF configurados.') }}</td>
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
