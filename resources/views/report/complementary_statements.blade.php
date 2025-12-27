@extends('layouts.admin')
@section('page-title')
    {{ __('Complementary Statements') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('Complementary Statements') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.complementary.statements'], 'method' => 'GET', 'id' => 'report_complementary_statements']) }}
                    <div class="row align-items-end">
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                {{ Form::date('start_date', $startDate, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                {{ Form::date('end_date', $endDate, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('report_complementary_statements').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('report.complementary.statements') }}" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="{{ __('Reset') }}">
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
        $equityHasLines = !empty($equityReport['increase']['lines']) || !empty($equityReport['decrease']['lines']);
        $cashFlowHasLines = !empty($cashFlowReport['operating']['lines']) || !empty($cashFlowReport['investing']['lines']) || !empty($cashFlowReport['financing']['lines']);
    @endphp

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-1">{{ __('Equity Variation Statement') }}</h5>
                            <p class="text-muted mb-0">{{ $companyName }}</p>
                            <p class="text-muted mb-0">{{ __('Period') }}: {{ $startDate }} - {{ $endDate }}</p>
                        </div>
                        <div class="d-flex">
                            {{ Form::open(['route' => ['report.equity.variation.export'], 'method' => 'POST', 'class' => 'me-2']) }}
                            {{ Form::hidden('start_date', $startDate) }}
                            {{ Form::hidden('end_date', $endDate) }}
                            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Export') }}">
                                <i class="ti ti-file-export"></i>
                            </button>
                            {{ Form::close() }}

                            {{ Form::open(['route' => ['report.equity.variation.pdf'], 'method' => 'POST']) }}
                            {{ Form::hidden('start_date', $startDate) }}
                            {{ Form::hidden('end_date', $endDate) }}
                            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                <i class="ti ti-download"></i>
                            </button>
                            {{ Form::close() }}
                        </div>
                    </div>

                    @if (!$equityHasLines)
                        <div class="alert alert-warning">{{ __('No statement mappings available.') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Line') }}</th>
                                        <th class="text-end">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (['increase', 'decrease'] as $sectionKey)
                                        @php
                                            $section = $equityReport[$sectionKey];
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
                                                <td colspan="2" class="text-center">{{ __('No data available') }}</td>
                                            </tr>
                                        @endforelse
                                        <tr class="fw-bold">
                                            <td>{{ __('Total') }} {{ $section['label'] }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($section['total']) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="fw-bold table-primary">
                                        <td>{{ __('Net Change in Equity') }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($equityReport['totals']['net_change']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-1">{{ __('Cash Flow Statement') }}</h5>
                            <p class="text-muted mb-0">{{ $companyName }}</p>
                            <p class="text-muted mb-0">{{ __('Period') }}: {{ $startDate }} - {{ $endDate }}</p>
                        </div>
                        <div class="d-flex">
                            {{ Form::open(['route' => ['report.cash.flow.export'], 'method' => 'POST', 'class' => 'me-2']) }}
                            {{ Form::hidden('start_date', $startDate) }}
                            {{ Form::hidden('end_date', $endDate) }}
                            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Export') }}">
                                <i class="ti ti-file-export"></i>
                            </button>
                            {{ Form::close() }}

                            {{ Form::open(['route' => ['report.cash.flow.pdf'], 'method' => 'POST']) }}
                            {{ Form::hidden('start_date', $startDate) }}
                            {{ Form::hidden('end_date', $endDate) }}
                            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                <i class="ti ti-download"></i>
                            </button>
                            {{ Form::close() }}
                        </div>
                    </div>

                    @if (!$cashFlowHasLines)
                        <div class="alert alert-warning">{{ __('No statement mappings available.') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Line') }}</th>
                                        <th class="text-end">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (['operating', 'investing', 'financing'] as $sectionKey)
                                        @php
                                            $section = $cashFlowReport[$sectionKey];
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
                                                <td colspan="2" class="text-center">{{ __('No data available') }}</td>
                                            </tr>
                                        @endforelse
                                        <tr class="fw-bold">
                                            <td>{{ __('Total') }} {{ $section['label'] }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($section['total']) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="fw-bold table-primary">
                                        <td>{{ __('Net Cash Flow') }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($cashFlowReport['totals']['net_cash']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
