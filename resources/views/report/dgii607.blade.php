@extends('layouts.admin')
@section('page-title')
    {{ __('DGII 607 (Ventas)') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('DGII 607') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Generar archivo 607 (DGII)') }}</h5>
            </div>
            <div class="card-body">
                {{ Form::open(['route' => 'report.dgii607', 'method' => 'GET', 'class' => 'row gy-3 align-items-end']) }}
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
                            <i class="ti ti-download"></i> {{ __('Descargar 607') }}
                        </button>
                    </div>
                {{ Form::close() }}
                <p class="text-muted mt-3 mb-0">
                    {{ __('Incluye las ventas facturadas del período con sus NCF, montos e ITBIS para el formato 607 de la DGII.') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
