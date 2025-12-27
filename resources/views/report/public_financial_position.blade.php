@extends('layouts.admin')
@section('page-title')
    {{ __('Estado de situación financiera pública') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Reporte') }}</li>
    <li class="breadcrumb-item">{{ __('Estado de situación financiera pública') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        {{ Form::open(['route' => ['report.public.financial.position.export'], 'method' => 'POST', 'class' => 'me-2', 'data-loading' => true, 'data-loading-text' => __('Generando exportación...')]) }}
        {{ Form::hidden('cutoff_date', $cutoffDate) }}
        <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Exportar') }}">
            <i class="ti ti-file-export"></i>
        </button>
        {{ Form::close() }}

        {{ Form::open(['route' => ['report.public.financial.position.pdf'], 'method' => 'POST', 'data-loading' => true, 'data-loading-text' => __('Generando PDF...')]) }}
        {{ Form::hidden('cutoff_date', $cutoffDate) }}
        <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Descargar') }}">
            <i class="ti ti-download"></i>
        </button>
        {{ Form::close() }}
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.public.financial.position'], 'method' => 'GET', 'id' => 'report_public_financial_position', 'data-loading' => true, 'data-loading-text' => __('Generando estado...')]) }}
                    <div class="row align-items-end">
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {{ Form::label('cutoff_date', __('Fecha de corte'), ['class' => 'form-label']) }}
                                {{ Form::date('cutoff_date', $cutoffDate, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('report_public_financial_position').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('Aplicar') }}">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('report.public.financial.position') }}" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="{{ __('Restablecer') }}">
                                <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off"></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    @php
        $creatorId = \Auth::user()->creatorId();
        $companyName = \App\Models\User::where('id', $creatorId)->value('name') ?? '';
        $hasLines = !empty($report['assets']['lines']) || !empty($report['liabilities']['lines']) || !empty($report['equity']['lines']);
    @endphp

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="mb-1">{{ __('Estado de situación financiera pública') }}</h5>
                        <p class="text-muted mb-0">{{ $companyName }}</p>
                        <p class="text-muted mb-0">{{ __('Fecha de corte') }}: {{ $cutoffDate }}</p>
                    </div>

                    @if (!$hasLines)
                        <x-alert type="warning">{{ __('No hay mapeos de estados disponibles.') }}</x-alert>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Línea') }}</th>
                                        <th class="text-end">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (['assets', 'liabilities', 'equity'] as $sectionKey)
                                        @php
                                            $section = $report[$sectionKey];
                                        @endphp
                                        <tr class="table-secondary">
                                            <th colspan="2">{{ $section['label'] }}</th>
                                        </tr>
                                        @forelse ($section['lines'] as $line)
                                            <tr>
                                                <td>{{ $line['name'] }}</td>
                                                <td class="text-end">{{ \Auth::user()->priceFormat($line['total']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center">{{ __('No hay datos disponibles') }}</td>
                                            </tr>
                                        @endforelse
                                        <tr class="fw-bold">
                                            <td>{{ __('Total') }} {{ $section['label'] }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($section['total']) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="fw-bold table-primary">
                                        <td>{{ __('Total pasivos y patrimonio') }}</td>
                                        <td class="text-end">
                                            {{ \Auth::user()->priceFormat($report['totals']['liabilities_equity']) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @include('report.partials.financial_notes', ['notes' => $notes])
                </div>
            </div>
        </div>
    </div>
@endsection
