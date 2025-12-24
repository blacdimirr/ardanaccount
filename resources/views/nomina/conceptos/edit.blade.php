{{ Form::model($concepto, ['route' => ['nomina-conceptos.update', $concepto->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('codigo', __('Código'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('codigo', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('nombre', __('Nombre'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('nombre', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tipo', __('Tipo'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('tipo', $tipos, null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('naturaleza', __('Naturaleza'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('naturaleza', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
