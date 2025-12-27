@extends('layouts.admin')
@section('page-title')
    {{ __('Public Budget Execution') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('Public Budget Execution') }}</li>
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: '{{ __('Public Budget Execution') }}',
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'a4',
                    orientation: 'landscape'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush

@section('action-btn')
    <div class="d-flex">
        <a href="#" class="btn btn-sm btn-primary me-2" onclick="saveAsPDF()" data-bs-toggle="tooltip"
            title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>
    </div>
    <div class="float-end me-2">
        {{ Form::open(['route' => ['report.public.budget.execution.export'], 'method' => 'POST']) }}
        {{ Form::hidden('budget_id', $selectedBudgetId) }}
        {{ Form::hidden('classifier', $classifier) }}
        <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Export') }}"
            data-original-title="{{ __('Export') }}"><i class="ti ti-file-export"></i></button>
        {{ Form::close() }}
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.public.budget.execution'], 'method' => 'GET', 'id' => 'report_public_budget_execution']) }}
                    <div class="row align-items-end">
                        <div class="col-xl-5 col-lg-6 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {{ Form::label('budget_id', __('Period'), ['class' => 'form-label']) }}
                                <select name="budget_id" class="form-control">
                                    <option value="">{{ __('Select Budget') }}</option>
                                    @foreach ($budgets as $budgetOption)
                                        @php
                                            $periodLabel = App\Models\Budget::$period[$budgetOption->period] ?? $budgetOption->period;
                                            $yearLabel = $budgetOption->from ?? '';
                                            $label = trim($budgetOption->name . ' - ' . $periodLabel . ' ' . $yearLabel);
                                        @endphp
                                        <option value="{{ $budgetOption->id }}"
                                            {{ (string) $budgetOption->id === (string) $selectedBudgetId ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                            <div class="btn-box">
                                {{ Form::label('classifier', __('Classifier'), ['class' => 'form-label']) }}
                                {{ Form::select('classifier', $classifierOptions, $classifier, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('report_public_budget_execution').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-original-title="{{ __('Apply') }}">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('report.public.budget.execution') }}" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="{{ __('Reset') }}" data-original-title="{{ __('Reset') }}">
                                <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off"></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="printableArea">
        @include('pdf.partials.header')
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="text-center">
                            <h5 class="mb-1">{{ $companyName !== '' ? $companyName : __('Institutional Report') }}</h5>
                            <h6 class="mb-1">{{ __('Public Budget Execution Report') }}</h6>
                        </div>
                        <p class="text-muted mb-0">{{ __('Period') }}: {{ $budgetLabel }}</p>
                        <p class="text-muted mb-0">{{ __('Classifier') }}: {{ $classifierOptions[$classifier] ?? $classifier }}</p>
                        <p class="text-muted mb-0">{{ __('Generated at') }}: {{ now()->format('Y-m-d H:i') }}</p>
                    </div>

                    @if ($budgets->isEmpty())
                        <div class="alert alert-warning">{{ __('No budget selected') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Classifier') }}</th>
                                        <th class="text-end">{{ __('PIA') }}</th>
                                        <th class="text-end">{{ __('PIM') }}</th>
                                        <th class="text-end">{{ __('Commitment') }}</th>
                                        <th class="text-end">{{ __('Accrued') }}</th>
                                        <th class="text-end">{{ __('Paid') }}</th>
                                        <th class="text-end">{{ __('Execution Balance') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                        <tr>
                                            <td>{{ $row['label'] }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($row['pia']) }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($row['pim']) }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($row['compromiso']) }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($row['devengado']) }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($row['pagado']) }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($row['saldo']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">{{ __('No data available') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td>{{ __('Total') }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($totals['pia']) }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($totals['pim']) }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($totals['compromiso']) }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($totals['devengado']) }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($totals['pagado']) }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($totals['saldo']) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
