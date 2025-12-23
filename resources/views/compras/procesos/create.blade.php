@extends('layouts.admin')

@section('page-title')
    {{ __('Create Purchase Process') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.procesos.index') }}">{{ __('Purchase Processes') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::open(['route' => 'compras.procesos.store', 'method' => 'post']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('requisicion_id', __('Requisition'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                <select name="requisicion_id" class="form-control select" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($requisiciones as $requisicion)
                                        <option value="{{ $requisicion->id }}">
                                            #{{ $requisicion->id }} - {{ $requisicion->area_solicitante }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('fecha_inicio', __('Start Date'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::date('fecha_inicio', old('fecha_inicio', now()->format('Y-m-d')), ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('descripcion', __('Description'), ['class' => 'form-label']) }}
                                {{ Form::textarea('descripcion', old('descripcion'), ['class' => 'form-control', 'rows' => 3]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                {{ Form::submit(__('Save'), ['class' => 'btn btn-primary']) }}
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
