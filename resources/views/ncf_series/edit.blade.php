{{ Form::model($ncfSeries, ['route' => ['ncf-series.update', $ncfSeries->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <x-alert-messages />

        @include('ncf_series._form')
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button>
    </div>
{{ Form::close() }}
