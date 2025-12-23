@extends('layouts.admin')

@section('page-title')
    {{ __('Create PAC') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pac.index') }}">{{ __('Plan Anual de Compras') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {!! Form::open(['route' => 'pac.store', 'method' => 'post']) !!}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('anio', __('Year'), ['class' => 'form-label']) }}<x-required></x-required>
                                {{ Form::select('anio', $years, old('anio', date('Y')), ['class' => 'form-control select', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('descripcion', __('Description'), ['class' => 'form-label']) }}
                                {{ Form::text('descripcion', old('descripcion'), ['class' => 'form-control', 'placeholder' => __('PAC description')]) }}
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
