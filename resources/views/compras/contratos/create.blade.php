@extends('layouts.admin')

@section('page-title')
    {{ __('Create Contract') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.contratos.index') }}">{{ __('Contracts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::open(['route' => 'compras.contratos.store', 'method' => 'post']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('adjudicacion_id', __('Award'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                <select name="adjudicacion_id" class="form-control select" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($adjudicaciones as $adjudicacion)
                                        <option value="{{ $adjudicacion->id }}">
                                            #{{ $adjudicacion->id }} - {{ $adjudicacion->oferta?->proveedor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('proveedor', __('Supplier'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::text('proveedor', old('proveedor'), ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('monto_contrato', __('Contract Amount'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::number('monto_contrato', old('monto_contrato'), ['class' => 'form-control', 'required' => true, 'step' => '0.01']) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('fecha_inicio', __('Start Date'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::date('fecha_inicio', old('fecha_inicio', now()->format('Y-m-d')), ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('fecha_fin', __('End Date'), ['class' => 'form-label']) }}
                                {{ Form::date('fecha_fin', old('fecha_fin'), ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('partida_presupuestaria_id', __('Budget Line'), ['class' => 'form-label']) }}
                                <select name="partida_presupuestaria_id" class="form-control select">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->name }}</option>
                                    @endforeach
                                </select>
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
