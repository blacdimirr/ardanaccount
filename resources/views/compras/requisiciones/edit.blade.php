@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Requisition') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.requisiciones.index') }}">{{ __('Requisitions') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::model($requisicion, ['route' => ['compras.requisiciones.update', $requisicion->id], 'method' => 'put']) !!}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('area_solicitante', __('Requesting Area'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::text('area_solicitante', null, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('fecha_requisicion', __('Requisition Date'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::date('fecha_requisicion', $requisicion->fecha_requisicion, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('estado', __('Status'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::select('estado', \App\Models\Requisicion::estados(), $requisicion->estado, ['class' => 'form-control select', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('descripcion', __('Description'), ['class' => 'form-label']) }}
                                {{ Form::textarea('descripcion', null, ['class' => 'form-control', 'rows' => 3]) }}
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
