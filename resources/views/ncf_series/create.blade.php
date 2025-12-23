{{ Form::open(['route' => 'ncf-series.store']) }}
    <div class="modal-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('ncf_series._form')
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
{{ Form::close() }}
