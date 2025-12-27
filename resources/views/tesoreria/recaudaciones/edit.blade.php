{{ Form::model($recaudacion, ['route' => ['tesoreria.recaudaciones.update', $recaudacion->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('fecha', __('Date'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::date('fecha', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('servicio', __('Service'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('servicio', $servicios, null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('monto', __('Amount'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::number('monto', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('metodo_pago', __('Payment Method'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('metodo_pago', $metodosPago, null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('cuenta_recaudadora_id', __('Collection Account'), ['class' => 'form-label']) }}<x-required></x-required>
            <select name="cuenta_recaudadora_id" class="form-control select" required>
                @foreach ($cuentas as $cuenta)
                    <option value="{{ $cuenta->id }}" {{ $cuenta->id == $recaudacion->cuenta_recaudadora_id ? 'selected' : '' }}>
                        {{ $cuenta->banco }} - {{ $cuenta->numero_cuenta }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('paciente_id', __('Patient ID (Optional)'), ['class' => 'form-label']) }}
            {{ Form::number('paciente_id', null, ['class' => 'form-control', 'min' => '1', 'placeholder' => __('Enter patient ID')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
