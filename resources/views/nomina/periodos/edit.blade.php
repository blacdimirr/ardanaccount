{{ Form::model($periodo, ['route' => ['nomina-periodos.update', $periodo->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('nombre', __('Nombre del periodo'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('nombre', null, ['class' => 'form-control', 'required' => 'required']) }}
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
    <div class="row mt-3">
        <div class="col-md-12">
            <h6 class="mb-2">{{ __('Vista previa del asiento por servicio') }}</h6>
            @if (!empty($resumen['services']))
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Servicio/Unidad') }}</th>
                                <th class="text-end">{{ __('Gastos') }}</th>
                                <th class="text-end">{{ __('Descuentos') }}</th>
                                <th class="text-end">{{ __('Neto') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resumen['services'] as $servicio)
                                <tr>
                                    <td>{{ $servicio['servicio'] }}</td>
                                    <td class="text-end">{{ \Auth::user()->priceFormat($servicio['gastos']) }}</td>
                                    <td class="text-end">{{ \Auth::user()->priceFormat($servicio['descuentos']) }}</td>
                                    <td class="text-end">{{ \Auth::user()->priceFormat($servicio['neto']) }}</td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold">
                                <td>{{ __('Total') }}</td>
                                <td class="text-end">{{ \Auth::user()->priceFormat($resumen['totales']['gastos'] ?? 0) }}</td>
                                <td class="text-end">{{ \Auth::user()->priceFormat($resumen['totales']['descuentos'] ?? 0) }}</td>
                                <td class="text-end">{{ \Auth::user()->priceFormat($resumen['totales']['neto'] ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted">{{ __('No hay detalles de nómina para generar el asiento.') }}</div>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
