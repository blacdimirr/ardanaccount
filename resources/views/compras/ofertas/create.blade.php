@extends('layouts.admin')

@section('page-title')
    {{ __('Register Offer') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.ofertas.index') }}">{{ __('Offers') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::open(['route' => 'compras.ofertas.store', 'method' => 'post']) !!}
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
                                {{ Form::label('proveedor', __('Supplier'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::text('proveedor', old('proveedor'), ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('monto_ofertado', __('Offered Amount'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::number('monto_ofertado', old('monto_ofertado'), ['class' => 'form-control', 'required' => true, 'step' => '0.01']) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('fecha_oferta', __('Offer Date'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::date('fecha_oferta', old('fecha_oferta', now()->format('Y-m-d')), ['class' => 'form-control', 'required' => true]) }}
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
