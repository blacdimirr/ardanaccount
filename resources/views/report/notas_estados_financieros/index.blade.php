@extends('layouts.admin')

@section('page-title')
    {{ __('Notas a los estados financieros') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('Notas a los estados financieros') }}</li>
@endsection

@section('action-btn')
    @can('reportes_notas_manage')
        <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="lg"
            data-title="{{ __('Nueva nota') }}" data-url="{{ route('report.notas.create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card mb-4">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.notas.index'], 'method' => 'GET']) }}
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="btn-box">
                                {{ Form::label('periodo', __('Periodo'), ['class' => 'form-label']) }}
                                {{ Form::date('periodo', $periodo, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="this.closest('form').submit(); return false;" data-bs-toggle="tooltip"
                                title="{{ __('Apply') }}">
                                <i class="ti ti-search"></i>
                            </a>
                            <a href="{{ route('report.notas.index') }}" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                <i class="ti ti-refresh text-white-off"></i>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Código') }}</th>
                                    <th>{{ __('Título') }}</th>
                                    <th>{{ __('Periodo') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th class="text-end">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notas as $nota)
                                    <tr>
                                        <td>{{ $nota->codigo_nota }}</td>
                                        <td>{{ $nota->titulo }}</td>
                                        <td>{{ optional($nota->periodo)->format('Y-m-d') }}</td>
                                        <td>
                                            @if ($nota->estado)
                                                <span class="badge bg-success">{{ __('Activo') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactivo') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="#" class="btn btn-sm btn-info me-2" data-ajax-popup="true"
                                                    data-size="lg" data-title="{{ __('Editar nota') }}"
                                                    data-url="{{ route('report.notas.edit', $nota->id) }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['report.notas.destroy', $nota->id], 'id' => 'delete-nota-' . $nota->id]) !!}
                                                <a href="#" class="btn btn-sm btn-danger bs-pass-para"
                                                    data-confirm="{{ __('¿Eliminar nota?') . '|' . __('Esta acción no se puede deshacer.') }}"
                                                    data-confirm-yes="document.getElementById('delete-nota-{{ $nota->id }}').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            {{ __('Aún no hay notas registradas.') }}
                                        </td>
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
