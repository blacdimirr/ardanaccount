{{ Form::model($tramo, ['route' => ['nomina.config_isr.update', $tramo->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-4">
            {{ Form::label('rango_desde', __('Desde'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::number('rango_desde', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => 0]) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('rango_hasta', __('Hasta'), ['class' => 'form-label']) }}
            {{ Form::number('rango_hasta', null, ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'placeholder' => __('Sin límite')]) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('tasa', __('Tasa (%)'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::number('tasa', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => 0, 'max' => 100]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
