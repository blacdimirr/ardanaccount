@extends('layouts.admin')
@section('page-title')
    {{ __('Bank Reconciliation Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('Bank Reconciliation Report') }}</li>
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
        <form method="POST" action="{{ route('report.conciliacion.bancaria.export') }}" class="me-2">
            @csrf
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <input type="hidden" name="estado" value="{{ $selectedEstado }}">
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
    <input type="hidden" id="filename" value="{{ 'bank_reconciliation_' . date('Y-m-d_H-i-s') }}" />
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.conciliacion.bancaria'], 'method' => 'GET', 'id' => 'report_conciliacion_bancaria']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-8">
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
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('estado', __('Status'), ['class' => 'text-type']) }}
                                        {{ Form::select('estado', ['' => __('All'), 'pendiente' => __('Pending'), 'conciliado' => __('Reconciled'), 'parcial' => __('Partial')], $selectedEstado, ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto d-flex mt-4">
                                    <a href="#" class="btn btn-sm btn-primary me-2"
                                        onclick="document.getElementById('report_conciliacion_bancaria').submit(); return false;"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('report.conciliacion.bancaria') }}" class="btn btn-sm btn-danger"
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
        @include('pdf.partials.header')
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6>{{ __('Totals by Status') }}</h6>
                </div>
                <div class="card-body table-border-style">
                    <table class="table">
                        <tbody>
                            @forelse ($totalsByStatus as $label => $total)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-end">{{ $total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted">{{ __('No data available.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Collection Account') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Matched Record') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movimientos as $movimiento)
                                    <tr>
                                        <td>{{ \Auth::user()->dateFormat($movimiento->fecha) }}</td>
                                        <td>{{ $movimiento->descripcion ?: '-' }}</td>
                                        <td>{{ $movimiento->referencia ?: '-' }}</td>
                                        <td>{{ \Auth::user()->priceFormat($movimiento->monto) }}</td>
                                        <td>
                                            {{ $movimiento->cuentaRecaudadora ? $movimiento->cuentaRecaudadora->banco . ' - ' . $movimiento->cuentaRecaudadora->numero_cuenta : '-' }}
                                        </td>
                                        <td>{{ ucfirst($movimiento->estado_conciliacion) }}</td>
                                        <td>
                                            @if ($movimiento->conciliable)
                                                @if ($movimiento->conciliable_type === \App\Models\Recaudacion::class)
                                                    {{ __('Collection') }} #{{ $movimiento->conciliable->id }}
                                                @elseif ($movimiento->conciliable_type === \App\Models\Payment::class)
                                                    {{ __('Supplier Payment') }} #{{ $movimiento->conciliable->id }}
                                                @else
                                                    -
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
