{{ Form::open(['route' => 'budget-classifiers.funding-sources.store', 'data-loading' => true, 'data-loading-text' => __('Guardando fuente de financiamiento...')]) }}
<div class="modal-body">
    @include('budget-classifiers.funding-sources._form')
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
</div>
{{ Form::close() }}
