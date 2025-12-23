<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('code', __('Código')) }}
            {{ Form::text('code', old('code', $ncfType->code ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 10]) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('description', __('Descripción')) }}
            {{ Form::text('description', old('description', $ncfType->description ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 255]) }}
        </div>
    </div>
    <div class="col-md-2 d-flex align-items-center">
        <div class="form-group form-check mt-4">
            {{ Form::hidden('active', 0) }}
            {{ Form::checkbox('active', 1, old('active', $ncfType->active ?? true), ['class' => 'form-check-input', 'id' => 'active']) }}
            {{ Form::label('active', __('Activo'), ['class' => 'form-check-label ms-2']) }}
        </div>
    </div>
</div>
