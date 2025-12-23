<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('ncf_type_id', __('Tipo de NCF')) }}
            {{ Form::select('ncf_type_id', $ncfTypes, old('ncf_type_id', $ncfSeries->ncf_type_id ?? ''), ['class' => 'form-control', 'required' => true, 'placeholder' => __('Seleccione un tipo')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {{ Form::label('series', __('Serie')) }}
            {{ Form::text('series', old('series', $ncfSeries->series ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 20]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {{ Form::label('status', __('Estado')) }}
            {{ Form::select('status', ['activo' => __('Activo'), 'vencido' => __('Vencido'), 'agotado' => __('Agotado')], old('status', $ncfSeries->status ?? 'activo'), ['class' => 'form-control', 'required' => true]) }}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('start_number', __('Número inicial')) }}
            {{ Form::number('start_number', old('start_number', $ncfSeries->start_number ?? null), ['class' => 'form-control', 'required' => true, 'min' => 1]) }}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('end_number', __('Número final')) }}
            {{ Form::number('end_number', old('end_number', $ncfSeries->end_number ?? null), ['class' => 'form-control', 'required' => true, 'min' => 1]) }}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('current_number', __('Número actual')) }}
            {{ Form::number('current_number', old('current_number', $ncfSeries->current_number ?? null), ['class' => 'form-control', 'min' => 1]) }}
            <small class="text-muted">{{ __('Deje en blanco para iniciar desde el número inicial - 1.') }}</small>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('valid_from', __('Válido desde')) }}
            {{ Form::date('valid_from', old('valid_from', optional(($ncfSeries->valid_from ?? null) ? \Carbon\Carbon::parse($ncfSeries->valid_from) : null)->format('Y-m-d')), ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('valid_to', __('Válido hasta')) }}
            {{ Form::date('valid_to', old('valid_to', optional(($ncfSeries->valid_to ?? null) ? \Carbon\Carbon::parse($ncfSeries->valid_to) : null)->format('Y-m-d')), ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="col-md-4 d-flex align-items-center">
        <div class="form-group form-check mt-4">
            {{ Form::hidden('active', 0) }}
            {{ Form::checkbox('active', 1, old('active', $ncfSeries->active ?? true), ['class' => 'form-check-input', 'id' => 'active']) }}
            {{ Form::label('active', __('Activo'), ['class' => 'form-check-label ms-2']) }}
        </div>
    </div>
</div>
