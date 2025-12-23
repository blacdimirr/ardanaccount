@extends('layouts.admin')

@section('page-title')
    {{ __('Create Award') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.adjudicaciones.index') }}">{{ __('Awards') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::open(['route' => 'compras.adjudicaciones.store', 'method' => 'post']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('proceso_compra_id', __('Purchase Process'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                <select name="proceso_compra_id" class="form-control select" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($procesos as $proceso)
                                        <option value="{{ $proceso->id }}">
                                            #{{ $proceso->id }} - {{ $proceso->requisicion?->area_solicitante }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('oferta_id', __('Offer'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                <select name="oferta_id" class="form-control select" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($procesos as $proceso)
                                        @foreach ($proceso->ofertas as $oferta)
                                            <option value="{{ $oferta->id }}">
                                                #{{ $oferta->id }} - {{ $oferta->proveedor }} ({{ \Auth::user()->priceFormat($oferta->monto_ofertado) }})
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('monto_adjudicado', __('Award Amount'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::number('monto_adjudicado', old('monto_adjudicado'), ['class' => 'form-control', 'required' => true, 'step' => '0.01']) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('fecha_adjudicacion', __('Award Date'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::date('fecha_adjudicacion', old('fecha_adjudicacion', now()->format('Y-m-d')), ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
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
