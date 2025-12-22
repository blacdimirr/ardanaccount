@extends('layouts.admin')

@section('page-title')
    {{ __('Editar regla de retención') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('retention-rules.index') }}">{{ __('Retenciones fiscales') }}</a></li>
    <li class="breadcrumb-item">{{ __('Editar') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::model($retentionRule, ['route' => ['retention-rules.update', $retentionRule->id], 'method' => 'PUT']) }}
                        @include('retention_rules._form')
                        <div class="mt-4 text-end">
                            <a href="{{ route('retention-rules.index') }}" class="btn btn-light">{{ __('Cancelar') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button>
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
