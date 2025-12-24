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
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
