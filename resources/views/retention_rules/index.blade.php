@extends('layouts.admin')

@section('page-title')
    {{ __('Retenciones fiscales') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Retenciones fiscales') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <a href="{{ route('retention-rules.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Nueva regla') }}">
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
                                    <th>{{ __('Tipo de sujeto') }}</th>
                                    <th>{{ __('Servicio / categoría') }}</th>
                                    <th>{{ __('% ITBIS retenido') }}</th>
                                    <th>{{ __('% ISR retenido') }}</th>
                                    <th>{{ __('% Retención gubernamental') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th>{{ __('Actualizado') }}</th>
                                    <th class="text-end">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $rule)
                                    <tr>
                                        <td>{{ $rule->supplier_type ?: __('General') }}</td>
                                        <td>{{ $rule->serviceCategory->name ?? __('Todas') }}</td>
                                        <td>{{ number_format($rule->itbis_retention_rate, 2) }}%</td>
                                        <td>{{ number_format($rule->isr_retention_rate, 2) }}%</td>
                                        <td>{{ number_format($rule->government_retention_rate, 2) }}%</td>
                                        <td>
                                            @if($rule->active)
                                                <span class="badge bg-success">{{ __('Activa') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactiva') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $rule->updated_at?->format('Y-m-d H:i') }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="{{ route('retention-rules.edit', $rule) }}" class="btn btn-sm btn-info me-2" data-bs-toggle="tooltip" title="{{ __('Editar') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                @if($rule->active)
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['retention-rules.destroy', $rule->id], 'id' => 'delete-form-'.$rule->id]) !!}
                                                        <a href="#" class="btn btn-sm btn-danger bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Desactivar') }}" data-confirm="{{ __('¿Desactivar regla?').'|'.__('Podrás reactivarla editando la configuración.') }}" data-confirm-yes="document.getElementById('delete-form-{{$rule->id}}').submit();">
                                                            <i class="ti ti-power text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">{{ __('Aún no hay reglas de retención configuradas.') }}</td>
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
