@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Offer') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.ofertas.index') }}">{{ __('Offers') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::model($oferta, ['route' => ['compras.ofertas.update', $oferta->id], 'method' => 'put']) !!}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('proveedor', __('Supplier'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::text('proveedor', null, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('monto_ofertado', __('Offered Amount'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::number('monto_ofertado', null, ['class' => 'form-control', 'required' => true, 'step' => '0.01']) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('fecha_oferta', __('Offer Date'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::date('fecha_oferta', $oferta->fecha_oferta, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('estado', __('Status'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::select('estado', \App\Models\Oferta::estados(), $oferta->estado, ['class' => 'form-control select', 'required' => true]) }}
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
