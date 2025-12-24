{{ Form::open(['url' => 'nomina-conceptos', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('codigo', __('Código'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('codigo', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Ingrese código')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('nombre', __('Nombre'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('nombre', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Ingrese nombre')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tipo', __('Tipo'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('tipo', $tipos, null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('naturaleza', __('Naturaleza'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('naturaleza', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Ej: Sueldo, Guardia, Incentivo')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('monto', __('Valor/Monto'), ['class' => 'form-label']) }}
            {{ Form::number('monto', 0, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) }}
        </div>
        <div class="form-group col-md-6 d-flex flex-column justify-content-end">
            <div class="form-check mb-2">
                {{ Form::checkbox('aplica_isr', 1, false, ['class' => 'form-check-input', 'id' => 'aplica_isr']) }}
                {{ Form::label('aplica_isr', __('Aplica ISR'), ['class' => 'form-check-label']) }}
            </div>
            <div class="form-check">
                {{ Form::checkbox('aplica_tss', 1, false, ['class' => 'form-check-input', 'id' => 'aplica_tss']) }}
                {{ Form::label('aplica_tss', __('Aplica TSS'), ['class' => 'form-check-label']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
