@extends('layouts.admin')

@section('page-title')
    {{ __('Clasificadores presupuestarios') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Configuración') }}</li>
    <li class="breadcrumb-item">{{ __('Clasificadores presupuestarios') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Objeto del gasto') }}</h5>
                    <a href="#" data-url="{{ route('budget-classifiers.objects.create') }}" data-ajax-popup="true"
                        data-size="md" data-title="{{ __('Nuevo objeto del gasto') }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Código') }}</th>
                                    <th>{{ __('Descripción') }}</th>
                                    <th>{{ __('Nivel') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th class="text-end">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($objects as $object)
                                    <tr>
                                        <td>{{ $object->code }}</td>
                                        <td>{{ $object->description }}</td>
                                        <td>{{ $object->level }}</td>
                                        <td>
                                            @if ($object->active)
                                                <span class="badge bg-success">{{ __('Activo') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactivo') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="#" class="btn btn-sm btn-info me-2"
                                                    data-url="{{ route('budget-classifiers.objects.edit', $object->id) }}"
                                                    data-ajax-popup="true" data-size="md"
                                                    data-title="{{ __('Editar objeto del gasto') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                @if ($object->active)
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['budget-classifiers.objects.destroy', $object->id], 'id' => 'delete-object-' . $object->id]) !!}
                                                    <a href="#" class="btn btn-sm btn-danger bs-pass-para"
                                                        data-confirm="{{ __('¿Desactivar objeto del gasto?') . '|' . __('Podrás reactivarlo editando el registro.') }}"
                                                        data-confirm-yes="document.getElementById('delete-object-{{ $object->id }}').submit();">
                                                        <i class="ti ti-power text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            {{ __('Aún no hay objetos del gasto configurados.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Fuentes de financiamiento') }}</h5>
                    <a href="#" data-url="{{ route('budget-classifiers.funding-sources.create') }}" data-ajax-popup="true"
                        data-size="md" data-title="{{ __('Nueva fuente de financiamiento') }}"
                        class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Código') }}</th>
                                    <th>{{ __('Descripción') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th class="text-end">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fundingSources as $fundingSource)
                                    <tr>
                                        <td>{{ $fundingSource->code }}</td>
                                        <td>{{ $fundingSource->description }}</td>
                                        <td>
                                            @if ($fundingSource->active)
                                                <span class="badge bg-success">{{ __('Activo') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactivo') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="#" class="btn btn-sm btn-info me-2"
                                                    data-url="{{ route('budget-classifiers.funding-sources.edit', $fundingSource->id) }}"
                                                    data-ajax-popup="true" data-size="md"
                                                    data-title="{{ __('Editar fuente de financiamiento') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                @if ($fundingSource->active)
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['budget-classifiers.funding-sources.destroy', $fundingSource->id], 'id' => 'delete-funding-' . $fundingSource->id]) !!}
                                                    <a href="#" class="btn btn-sm btn-danger bs-pass-para"
                                                        data-confirm="{{ __('¿Desactivar fuente de financiamiento?') . '|' . __('Podrás reactivarla editando el registro.') }}"
                                                        data-confirm-yes="document.getElementById('delete-funding-{{ $fundingSource->id }}').submit();">
                                                        <i class="ti ti-power text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            {{ __('Aún no hay fuentes de financiamiento configuradas.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Programas') }}</h5>
                    <a href="#" data-url="{{ route('budget-classifiers.programs.create') }}" data-ajax-popup="true"
                        data-size="md" data-title="{{ __('Nuevo programa') }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Código') }}</th>
                                    <th>{{ __('Nombre') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th class="text-end">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programs as $program)
                                    <tr>
                                        <td>{{ $program->code }}</td>
                                        <td>{{ $program->name }}</td>
                                        <td>
                                            @if ($program->active)
                                                <span class="badge bg-success">{{ __('Activo') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactivo') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="#" class="btn btn-sm btn-info me-2"
                                                    data-url="{{ route('budget-classifiers.programs.edit', $program->id) }}"
                                                    data-ajax-popup="true" data-size="md"
                                                    data-title="{{ __('Editar programa') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                @if ($program->active)
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['budget-classifiers.programs.destroy', $program->id], 'id' => 'delete-program-' . $program->id]) !!}
                                                    <a href="#" class="btn btn-sm btn-danger bs-pass-para"
                                                        data-confirm="{{ __('¿Desactivar programa?') . '|' . __('Podrás reactivarlo editando el registro.') }}"
                                                        data-confirm-yes="document.getElementById('delete-program-{{ $program->id }}').submit();">
                                                        <i class="ti ti-power text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            {{ __('Aún no hay programas configurados.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Proyectos') }}</h5>
                    <a href="#" data-url="{{ route('budget-classifiers.projects.create') }}" data-ajax-popup="true"
                        data-size="md" data-title="{{ __('Nuevo proyecto') }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Código') }}</th>
                                    <th>{{ __('Nombre') }}</th>
                                    <th>{{ __('Programa') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th class="text-end">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                    <tr>
                                        <td>{{ $project->code }}</td>
                                        <td>{{ $project->name }}</td>
                                        <td>{{ $project->program?->name }}</td>
                                        <td>
                                            @if ($project->active)
                                                <span class="badge bg-success">{{ __('Activo') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactivo') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="#" class="btn btn-sm btn-info me-2"
                                                    data-url="{{ route('budget-classifiers.projects.edit', $project->id) }}"
                                                    data-ajax-popup="true" data-size="md"
                                                    data-title="{{ __('Editar proyecto') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                @if ($project->active)
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['budget-classifiers.projects.destroy', $project->id], 'id' => 'delete-project-' . $project->id]) !!}
                                                    <a href="#" class="btn btn-sm btn-danger bs-pass-para"
                                                        data-confirm="{{ __('¿Desactivar proyecto?') . '|' . __('Podrás reactivarlo editando el registro.') }}"
                                                        data-confirm-yes="document.getElementById('delete-project-{{ $project->id }}').submit();">
                                                        <i class="ti ti-power text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            {{ __('Aún no hay proyectos configurados.') }}</td>
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
