@extends('layouts.admin')
@section('page-title')
    {{ __('DGII 606 (Compras)') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('DGII 606') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Generar archivo 606 (DGII)') }}</h5>
            </div>
            <div class="card-body">
                {{ Form::open(['route' => 'report.dgii606', 'method' => 'GET', 'class' => 'row gy-3 align-items-end', 'data-loading' => true, 'data-loading-text' => __('Generando 606...')]) }}
                    <div class="col-md-4">
                        {{ Form::label('month', __('Mes'), ['class' => 'form-label']) }}
                        {{ Form::select('month', $months, $selectedMonth, ['class' => 'form-control select']) }}
                    </div>
                    <div class="col-md-4">
                        {{ Form::label('year', __('Año'), ['class' => 'form-label']) }}
                        {{ Form::select('year', $years, $selectedYear, ['class' => 'form-control select']) }}
                    </div>
                    <div class="col-md-4">
                        {{ Form::hidden('action', 'download') }}
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-download"></i> {{ __('Descargar 606') }}
                        </button>
                    </div>
                {{ Form::close() }}
                <p class="text-muted mt-3 mb-0">
                    {{ __('El archivo incluye las compras y gastos del período seleccionado usando los campos de NCF e ITBIS/ISR retenido ya registrados en los documentos.') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
