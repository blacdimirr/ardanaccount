@extends('layouts.admin')
@section('page-title')
    {{ __('Fund Movements Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item">{{ __('Fund Movements Report') }}</li>
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
        <form method="POST" action="{{ route('report.fondos.movimientos.export') }}" class="me-2">
            @csrf
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <input type="hidden" name="fondo_id" value="{{ $selectedFondo }}">
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
    <input type="hidden" id="filename" value="{{ 'fund_movements_' . date('Y-m-d_H-i-s') }}" />
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.fondos.movimientos'], 'method' => 'GET', 'id' => 'report_fondos_movimientos']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('start_date', __('Start Date'), ['class' => 'text-type']) }}
                                        {{ Form::date('start_date', $startDate, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('end_date', __('End Date'), ['class' => 'text-type']) }}
                                        {{ Form::date('end_date', $endDate, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('fondo_id', __('Fund'), ['class' => 'text-type']) }}
                                        {{ Form::select('fondo_id', $fondos->pluck('nombre', 'id')->prepend(__('All'), ''), $selectedFondo, ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto d-flex mt-4">
                                    <a href="#" class="btn btn-sm btn-primary me-2"
                                        onclick="document.getElementById('report_fondos_movimientos').submit(); return false;"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('report.fondos.movimientos') }}" class="btn btn-sm btn-danger"
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

    <div class="row">
        <div class="col-12">
            <div class="card" id="printableArea">
        @include('pdf.partials.header')
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Fund') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Receipt/Voucher') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movimientos as $movimiento)
                                    <tr>
                                        <td>{{ \Auth::user()->dateFormat($movimiento->fecha) }}</td>
                                        <td>{{ $movimiento->fondo?->nombre }}</td>
                                        <td>{{ $movimiento->tipo === 'egreso' ? __('Expense') : __('Replenishment') }}</td>
                                        <td>{{ \Auth::user()->priceFormat($movimiento->monto) }}</td>
                                        <td>{{ $movimiento->descripcion ?: '-' }}</td>
                                        <td>{{ $movimiento->comprobante_id ?: '-' }}</td>
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
