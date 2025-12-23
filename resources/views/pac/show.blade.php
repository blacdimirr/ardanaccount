@extends('layouts.admin')

@section('page-title')
    {{ __('PAC') }} {{ $pac->anio }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pac.index') }}">{{ __('Plan Anual de Compras') }}</a></li>
    <li class="breadcrumb-item">{{ __('Detail') }}</li>
@endsection

@section('action-btn')
    <div class="d-flex">
        <a href="{{ route('pac.report', Crypt::encrypt($pac->id)) }}" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="tooltip" title="{{ __('Download PDF') }}">
            <i class="ti ti-file-download"></i>
        </a>
        <a href="{{ route('pac.export', Crypt::encrypt($pac->id)) }}" class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="tooltip" title="{{ __('Export Excel') }}">
            <i class="ti ti-file-spreadsheet"></i>
        </a>
        <a href="{{ route('pac.edit', Crypt::encrypt($pac->id)) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
            <i class="ti ti-pencil"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">{{ __('Year') }}</h6>
                            <p class="mb-0">{{ $pac->anio }}</p>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted mb-1">{{ __('Description') }}</h6>
                            <p class="mb-0">{{ $pac->descripcion }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('PAC Items') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Budget Line') }}</th>
                                    <th>{{ __('Object of Expense') }}</th>
                                    <th>{{ __('Funding Source') }}</th>
                                    <th>{{ __('Procedure Type') }}</th>
                                    <th class="text-end">{{ __('Estimated Amount') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pac->items as $item)
                                    <tr>
                                        <td>{{ $item->descripcion }}</td>
                                        <td>{{ $item->partidaPresupuestaria->name ?? '' }}</td>
                                        <td>{{ $item->objetoGasto ? $item->objetoGasto->code . ' - ' . $item->objetoGasto->description : '' }}</td>
                                        <td>{{ $item->fuenteFinanciamiento ? $item->fuenteFinanciamiento->code . ' - ' . $item->fuenteFinanciamiento->description : '' }}</td>
                                        <td>{{ $item->tipo_procedimiento }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($item->monto_estimado) }}</td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-bs-toggle="modal" data-bs-target="#edit-item-{{ $item->id }}" title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['pac.items.destroy', $pac->id, $item->id], 'id' => 'delete-item-' . $item->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para bg-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-item-{{ $item->id }}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('No items added yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($pac->items->count() > 0)
                                <tfoot>
                                    <tr>
                                        <th colspan="5">{{ __('Total') }}</th>
                                        <th class="text-end">{{ \Auth::user()->priceFormat($pac->items->sum('monto_estimado')) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Add Item') }}</h5>
                </div>
                <div class="card-body">
                    {!! Form::open(['route' => ['pac.items.store', $pac->id], 'method' => 'post']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('descripcion', __('Description'), ['class' => 'form-label']) }}<x-required></x-required>
                                {{ Form::text('descripcion', old('descripcion'), ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('partida_presupuestaria_id', __('Budget Line'), ['class' => 'form-label']) }}<x-required></x-required>
                                <select name="partida_presupuestaria_id" class="form-control select" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('partida_presupuestaria_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('objeto_gasto_id', __('Object of Expense'), ['class' => 'form-label']) }}<x-required></x-required>
                                <select name="objeto_gasto_id" class="form-control select" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($objects as $object)
                                        <option value="{{ $object->id }}" {{ old('objeto_gasto_id') == $object->id ? 'selected' : '' }}>
                                            {{ $object->code }} - {{ $object->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('fuente_financiamiento_id', __('Funding Source'), ['class' => 'form-label']) }}<x-required></x-required>
                                <select name="fuente_financiamiento_id" class="form-control select" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($fundingSources as $source)
                                        <option value="{{ $source->id }}" {{ old('fuente_financiamiento_id') == $source->id ? 'selected' : '' }}>
                                            {{ $source->code }} - {{ $source->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('tipo_procedimiento', __('Procedure Type'), ['class' => 'form-label']) }}<x-required></x-required>
                                {{ Form::text('tipo_procedimiento', old('tipo_procedimiento'), ['class' => 'form-control', 'required' => true]) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('monto_estimado', __('Estimated Amount'), ['class' => 'form-label']) }}<x-required></x-required>
                                {{ Form::number('monto_estimado', old('monto_estimado'), ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'required' => true]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                {{ Form::submit(__('Add Item'), ['class' => 'btn btn-primary']) }}
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    @foreach ($pac->items as $item)
        <div class="modal fade" id="edit-item-{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Edit Item') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {!! Form::open(['route' => ['pac.items.update', $pac->id, $item->id], 'method' => 'PUT']) !!}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('descripcion', __('Description'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {{ Form::text('descripcion', $item->descripcion, ['class' => 'form-control', 'required' => true]) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('partida_presupuestaria_id', __('Budget Line'), ['class' => 'form-label']) }}<x-required></x-required>
                                    <select name="partida_presupuestaria_id" class="form-control select" required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ $item->partida_presupuestaria_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('objeto_gasto_id', __('Object of Expense'), ['class' => 'form-label']) }}<x-required></x-required>
                                    <select name="objeto_gasto_id" class="form-control select" required>
                                        @foreach ($objects as $object)
                                            <option value="{{ $object->id }}" {{ $item->objeto_gasto_id == $object->id ? 'selected' : '' }}>
                                                {{ $object->code }} - {{ $object->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('fuente_financiamiento_id', __('Funding Source'), ['class' => 'form-label']) }}<x-required></x-required>
                                    <select name="fuente_financiamiento_id" class="form-control select" required>
                                        @foreach ($fundingSources as $source)
                                            <option value="{{ $source->id }}" {{ $item->fuente_financiamiento_id == $source->id ? 'selected' : '' }}>
                                                {{ $source->code }} - {{ $source->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('tipo_procedimiento', __('Procedure Type'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {{ Form::text('tipo_procedimiento', $item->tipo_procedimiento, ['class' => 'form-control', 'required' => true]) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('monto_estimado', __('Estimated Amount'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {{ Form::number('monto_estimado', $item->monto_estimado, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'required' => true]) }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
