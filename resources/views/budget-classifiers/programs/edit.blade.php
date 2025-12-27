{{ Form::model($program, ['route' => ['budget-classifiers.programs.update', $program->id], 'method' => 'PUT', 'data-loading' => true, 'data-loading-text' => __('Actualizando programa...')]) }}
<div class="modal-body">
    @include('budget-classifiers.programs._form')
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button>
</div>
{{ Form::close() }}
