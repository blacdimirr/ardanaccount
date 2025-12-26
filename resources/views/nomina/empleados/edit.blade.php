{{ Form::model($empleado, ['route' => ['nomina-empleados.update', $empleado->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('first_name', __('Nombres'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('first_name', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('last_name', __('Apellidos'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('last_name', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('documento_identidad', __('Documento de identidad'), ['class' => 'form-label']) }}
            {{ Form::text('documento_identidad', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::email('email', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('telefono', __('Teléfono'), ['class' => 'form-label']) }}
            {{ Form::text('telefono', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('direccion', __('Dirección'), ['class' => 'form-label']) }}
            {{ Form::text('direccion', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tipo_vinculo', __('Tipo vínculo'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('tipo_vinculo', $tiposVinculo, null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tipo_contribuyente', __('Tipo de contribuyente'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('tipo_contribuyente', $tiposContribuyente, null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('servicio_id', __('Servicio/Unidad registrada'), ['class' => 'form-label']) }}
            {{ Form::select('servicio_id', ['' => __('Seleccionar servicio')] + $servicios->pluck('nombre', 'id')->toArray(), $empleado->servicio_id, ['class' => 'form-control select']) }}
            <small class="text-muted">{{ __('Seleccione un servicio para asociarlo al empleado si está registrado.') }}</small>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('unidad_servicio', __('Unidad/Servicio'), ['class' => 'form-label']) }}
            {{ Form::text('unidad_servicio', null, ['class' => 'form-control']) }}
            <small class="text-muted">{{ __('Este campo es obligatorio si no selecciona un servicio registrado.') }}</small>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('salario', __('Salario base'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::number('salario', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => 0]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
