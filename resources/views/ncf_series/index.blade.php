@extends('layouts.admin')

@section('page-title')
    {{ __('Series de NCF') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Configuración') }}</li>
    <li class="breadcrumb-item">{{ __('Series de NCF') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <a href="#" data-url="{{ route('ncf-series.create') }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Nueva serie de NCF') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Nueva serie de NCF') }}">
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
                                    <th>{{ __('Tipo') }}</th>
                                    <th>{{ __('Serie') }}</th>
                                    <th>{{ __('Rango') }}</th>
                                    <th>{{ __('Número actual') }}</th>
                                    <th>{{ __('Vigencia') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th class="text-end">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($series as $item)
                                    <tr>
                                        <td>{{ optional($item->type)->code }} - {{ optional($item->type)->description }}</td>
                                        <td>{{ $item->series }}</td>
                                        <td>{{ $item->start_number }} - {{ $item->end_number }}</td>
                                        <td>{{ $item->current_number ?? __('No asignado') }}</td>
                                        <td>
                                            @if($item->valid_from || $item->valid_to)
                                                {{ $item->valid_from ?? '...' }} - {{ $item->valid_to ?? '...' }}
                                            @else
                                                <span class="text-muted">{{ __('Sin fecha') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-{{ $item->status === 'activo' ? 'success' : ($item->status === 'vencido' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                                <small class="text-muted">{{ $item->active ? __('Activo') : __('Inactivo') }}</small>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="#" class="btn btn-sm btn-info me-2" data-url="{{ route('ncf-series.edit', $item->id) }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Editar serie de NCF') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                @if($item->active)
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['ncf-series.destroy', $item->id], 'id' => 'delete-form-'.$item->id]) !!}
                                                        <a href="#" class="btn btn-sm btn-danger bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Desactivar') }}" data-confirm="{{ __('¿Desactivar serie de NCF?').'|'.__('Podrás reactivarla editando la configuración.') }}" data-confirm-yes="document.getElementById('delete-form-{{$item->id}}').submit();">
                                                            <i class="ti ti-power text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">{{ __('Aún no hay series de NCF configuradas.') }}</td>
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
