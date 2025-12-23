@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Award') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compras.adjudicaciones.index') }}">{{ __('Awards') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::model($adjudicacion, ['route' => ['compras.adjudicaciones.update', $adjudicacion->id], 'method' => 'put']) !!}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('monto_adjudicado', __('Award Amount'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::number('monto_adjudicado', null, ['class' => 'form-control', 'required' => true, 'step' => '0.01']) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('fecha_adjudicacion', __('Award Date'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::date('fecha_adjudicacion', $adjudicacion->fecha_adjudicacion, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('estado', __('Status'), ['class' => 'form-label']) }}
                                <x-required></x-required>
                                {{ Form::select('estado', \App\Models\Adjudicacion::estados(), $adjudicacion->estado, ['class' => 'form-control select', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('partida_presupuestaria_id', __('Budget Line'), ['class' => 'form-label']) }}
                                <select name="partida_presupuestaria_id" class="form-control select">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ $adjudicacion->partida_presupuestaria_id == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->name }}
                                        </option>
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
