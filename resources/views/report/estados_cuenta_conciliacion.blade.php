@extends('layouts.admin')
@section('page-title')
    {{ __('Account Statement & Reconciliation') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('Account Statement & Reconciliation') }}</li>
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var filename = document.getElementById('filename').value;
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A4'}
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush

@section('action-btn')
    <div class="d-flex">
        <form method="POST" action="{{ route('report.estados.cuenta.conciliacion.export') }}" class="me-2">
            @csrf
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <input type="hidden" name="cuenta_recaudadora_id" value="{{ $selectedCuenta }}">
            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Export') }}">
                <i class="ti ti-file-export"></i>
            </button>
        </form>
        <button type="button" class="btn btn-sm btn-secondary" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{ __('Download') }}">
            <i class="ti ti-download"></i>
        </button>
    </div>
@endsection

@section('content')
    <input type="hidden" id="filename" value="{{ 'account_statement_reconciliation_' . date('Y-m-d_H-i-s') }}" />
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.estados.cuenta.conciliacion'], 'method' => 'GET', 'id' => 'report_estado_cuenta_conciliacion']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-9">
                            <div class="row">
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('start_date', __('Start Date'), ['class' => 'text-type']) }}
                                        {{ Form::date('start_date', $startDate, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('end_date', __('End Date'), ['class' => 'text-type']) }}
                                        {{ Form::date('end_date', $endDate, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('cuenta_recaudadora_id', __('Collection Account'), ['class' => 'text-type']) }}
                                        {{ Form::select('cuenta_recaudadora_id', ['' => __('All Accounts')] + $cuentaOptions, $selectedCuenta, ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto d-flex mt-4">
                                    <a href="#" class="btn btn-sm btn-primary me-2"
                                        onclick="document.getElementById('report_estado_cuenta_conciliacion').submit(); return false;"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('report.estados.cuenta.conciliacion') }}" class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="printableArea">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6>{{ __('Statement Summary') }}</h6>
                </div>
                <div class="card-body table-border-style">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>{{ __('Opening Balance') }}</td>
                                <td class="text-end">{{ \Auth::user()->priceFormat($statementData['opening_balance']) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Period Total') }}</td>
                                <td class="text-end">{{ \Auth::user()->priceFormat($statementData['period_total']) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Closing Balance') }}</td>
                                <td class="text-end">{{ \Auth::user()->priceFormat($statementData['closing_balance']) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Total Differences') }}</td>
                                <td class="text-end">{{ \Auth::user()->priceFormat($differenceTotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>{{ __('Statement Movements') }}</h6>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Matched Record') }}</th>
                                    <th>{{ __('Matched Amount') }}</th>
                                    <th>{{ __('Running Balance') }}</th>
                                    <th>{{ __('Collection Account') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($statementData['rows'] as $row)
                                    <tr>
                                        <td>{{ \Auth::user()->dateFormat($row['movimiento']->fecha) }}</td>
                                        <td>{{ $row['movimiento']->descripcion ?: '-' }}</td>
                                        <td>{{ $row['movimiento']->referencia ?: '-' }}</td>
                                        <td>{{ \Auth::user()->priceFormat($row['movimiento']->monto) }}</td>
                                        <td>{{ ucfirst($row['movimiento']->estado_conciliacion) }}</td>
                                        <td>{{ $row['matched_label'] }}</td>
                                        <td>
                                            {{ $row['matched_amount'] !== null ? \Auth::user()->priceFormat($row['matched_amount']) : '-' }}
                                        </td>
                                        <td>{{ \Auth::user()->priceFormat($row['saldo']) }}</td>
                                        <td>
                                            {{ $row['movimiento']->cuentaRecaudadora
                                                ? $row['movimiento']->cuentaRecaudadora->banco . ' - ' . $row['movimiento']->cuentaRecaudadora->numero_cuenta
                                                : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">{{ __('No data available.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>{{ __('Reconciliation Differences') }}</h6>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Matched Record') }}</th>
                                    <th>{{ __('Matched Amount') }}</th>
                                    <th>{{ __('Difference') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($differenceRows as $row)
                                    <tr>
                                        <td>{{ \Auth::user()->dateFormat($row['movimiento']->fecha) }}</td>
                                        <td>{{ $row['movimiento']->descripcion ?: '-' }}</td>
                                        <td>{{ $row['movimiento']->referencia ?: '-' }}</td>
                                        <td>{{ \Auth::user()->priceFormat($row['movimiento']->monto) }}</td>
                                        <td>{{ ucfirst($row['movimiento']->estado_conciliacion) }}</td>
                                        <td>{{ $row['matched_label'] }}</td>
                                        <td>
                                            {{ $row['matched_amount'] !== null ? \Auth::user()->priceFormat($row['matched_amount']) : '-' }}
                                        </td>
                                        <td>{{ \Auth::user()->priceFormat($row['difference']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">{{ __('No data available.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
