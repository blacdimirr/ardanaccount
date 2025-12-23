@extends('layouts.admin')
@section('page-title')
    {{ __('DGII 608 (Anulaciones)') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('DGII 608') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Generar archivo 608 (DGII)') }}</h5>
            </div>
            <div class="card-body">
                {{ Form::open(['route' => 'report.dgii608', 'method' => 'GET', 'class' => 'row gy-3 align-items-end']) }}
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
                            <i class="ti ti-download"></i> {{ __('Descargar 608') }}
                        </button>
                    </div>
                {{ Form::close() }}
                <p class="text-muted mt-3 mb-0">
                    {{ __('Incluye las notas de crédito y anulaciones del período con NCF referenciado y montos para el formato 608 de la DGII.') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
