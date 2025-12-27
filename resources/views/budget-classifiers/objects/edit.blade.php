{{ Form::model($classifier, ['route' => ['budget-classifiers.objects.update', $classifier->id], 'method' => 'PUT', 'data-loading' => true, 'data-loading-text' => __('Actualizando clasificador...')]) }}
<div class="modal-body">
    @include('budget-classifiers.objects._form')
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button>
</div>
{{ Form::close() }}
