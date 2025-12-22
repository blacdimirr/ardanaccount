@extends('layouts.admin')

@section('page-title')
    {{ __('Nueva regla de retención') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('retention-rules.index') }}">{{ __('Retenciones fiscales') }}</a></li>
    <li class="breadcrumb-item">{{ __('Crear') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => 'retention-rules.store']) }}
                        @include('retention_rules._form', ['retentionRule' => new \App\Models\RetentionRule()])
                        <div class="mt-4 text-end">
                            <a href="{{ route('retention-rules.index') }}" class="btn btn-light">{{ __('Cancelar') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
