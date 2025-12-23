{{ Form::model($ncfType, ['route' => ['ncf-types.update', $ncfType->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        @include('ncf_types._form')
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button>
    </div>
{{ Form::close() }}
