{{ Form::open(['route' => ['tesoreria.fondos.movimientos.store', $fondo->id], 'class'=>'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            <label class="form-label">{{ __('Fund') }}</label>
            <div class="form-control-plaintext fw-bold">{{ $fondo->nombre }}</div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tipo', __('Movement Type'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('tipo', ['egreso' => __('Expense'), 'reposicion' => __('Replenishment')], null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('monto', __('Amount'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                <span><i class="ti ti-cash"></i></span>
                {{ Form::number('monto', '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01', 'placeholder'=>__('Enter amount')]) }}
            </div>
            <div class="text-xs mt-1">
                {{ __('Available Amount') }}: {{ \Auth::user()->priceFormat($fondo->monto_disponible) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('fecha', __('Date'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::date('fecha', now()->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('comprobante_id', __('Receipt/Voucher'), ['class' => 'form-label']) }}
            {{ Form::number('comprobante_id', '', ['class' => 'form-control', 'placeholder'=>__('Enter voucher id')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('descripcion', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('descripcion', '', ['class' => 'form-control', 'rows' => 2, 'placeholder'=>__('Add a description')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Save') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
