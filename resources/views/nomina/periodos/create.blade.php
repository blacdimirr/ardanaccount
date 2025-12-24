{{ Form::open(['url' => 'nomina-periodos', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('nombre', __('Nombre del periodo'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('nombre', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Ej: Enero 2024')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('fecha_inicio', __('Fecha inicio'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::date('fecha_inicio', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('fecha_fin', __('Fecha fin'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::date('fecha_fin', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('estado', __('Estado'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('estado', $estados, null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
