@extends('layouts.admin')
@section('page-title')
    {{ __('Complementary Statement Mappings') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report') }}</li>
    <li class="breadcrumb-item"><a href="{{ route('report.complementary.statements') }}">{{ __('Complementary Statements') }}</a></li>
    <li class="breadcrumb-item">{{ __('Mappings') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Equity Variation Mappings') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => ['report.equity.mappings.store'], 'method' => 'POST']) }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('line_name', __('Line Name'), ['class' => 'form-label']) }}
                                {{ Form::text('line_name', null, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('section', __('Section'), ['class' => 'form-label']) }}
                                {{ Form::select('section', ['increase' => __('Increases in Equity'), 'decrease' => __('Decreases in Equity')], null, ['class' => 'form-control select', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('chart_of_account_id', __('Account'), ['class' => 'form-label']) }}
                                <select name="chart_of_account_id" class="form-control select">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('sort_order', __('Order'), ['class' => 'form-label']) }}
                                {{ Form::number('sort_order', 0, ['class' => 'form-control', 'min' => 0]) }}
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">{{ __('Add Mapping') }}</button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Section') }}</th>
                                <th>{{ __('Line') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th class="text-end">{{ __('Order') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($equityMappings as $mapping)
                                <tr>
                                    <td>{{ $mapping->section === 'increase' ? __('Increases in Equity') : __('Decreases in Equity') }}</td>
                                    <td>{{ $mapping->line_name }}</td>
                                    <td>{{ $mapping->account ? $mapping->account->code . ' - ' . $mapping->account->name : __('All') }}</td>
                                    <td class="text-end">{{ $mapping->sort_order }}</td>
                                    <td class="text-end">
                                        {{ Form::open(['route' => ['report.equity.mappings.destroy', $mapping->id], 'method' => 'DELETE']) }}
                                        <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                        {{ Form::close() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('No mappings available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Cash Flow Mappings') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => ['report.cashflow.mappings.store'], 'method' => 'POST']) }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('line_name', __('Line Name'), ['class' => 'form-label']) }}
                                {{ Form::text('line_name', null, ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('section', __('Section'), ['class' => 'form-label']) }}
                                {{ Form::select('section', [
                                    'operating' => __('Operating Activities'),
                                    'investing' => __('Investing Activities'),
                                    'financing' => __('Financing Activities'),
                                ], null, ['class' => 'form-control select', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                {{ Form::label('chart_of_account_id', __('Account'), ['class' => 'form-label']) }}
                                <select name="chart_of_account_id" class="form-control select">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {{ Form::label('sort_order', __('Order'), ['class' => 'form-label']) }}
                                {{ Form::number('sort_order', 0, ['class' => 'form-control', 'min' => 0]) }}
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">{{ __('Add Mapping') }}</button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Section') }}</th>
                                <th>{{ __('Line') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th class="text-end">{{ __('Order') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cashFlowMappings as $mapping)
                                <tr>
                                    <td>
                                        @if ($mapping->section === 'operating')
                                            {{ __('Operating Activities') }}
                                        @elseif ($mapping->section === 'investing')
                                            {{ __('Investing Activities') }}
                                        @else
                                            {{ __('Financing Activities') }}
                                        @endif
                                    </td>
                                    <td>{{ $mapping->line_name }}</td>
                                    <td>{{ $mapping->account ? $mapping->account->code . ' - ' . $mapping->account->name : __('All') }}</td>
                                    <td class="text-end">{{ $mapping->sort_order }}</td>
                                    <td class="text-end">
                                        {{ Form::open(['route' => ['report.cashflow.mappings.destroy', $mapping->id], 'method' => 'DELETE']) }}
                                        <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                        {{ Form::close() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('No mappings available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
