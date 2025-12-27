{{ Form::model($nota, ['route' => ['report.notas.update', $nota->id], 'method' => 'PUT']) }}
<div class="modal-body">
    @include('report.notas_estados_financieros._form', ['nota' => $nota])
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button>
</div>
{{ Form::close() }}
