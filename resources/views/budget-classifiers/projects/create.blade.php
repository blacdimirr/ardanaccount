{{ Form::open(['route' => 'budget-classifiers.projects.store']) }}
<div class="modal-body">
    @include('budget-classifiers.projects._form')
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
</div>
{{ Form::close() }}
