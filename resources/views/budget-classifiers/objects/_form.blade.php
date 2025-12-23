<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('code', __('Código')) }}
            {{ Form::text('code', old('code', $classifier->code ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 50]) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('description', __('Descripción')) }}
            {{ Form::text('description', old('description', $classifier->description ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 255]) }}
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            {{ Form::label('level', __('Nivel')) }}
            {{ Form::number('level', old('level', $classifier->level ?? 1), ['class' => 'form-control', 'required' => true, 'min' => 1]) }}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group form-check mt-2">
            {{ Form::hidden('active', 0) }}
            {{ Form::checkbox('active', 1, old('active', $classifier->active ?? true), ['class' => 'form-check-input', 'id' => 'object-active']) }}
            {{ Form::label('object-active', __('Activo'), ['class' => 'form-check-label ms-2']) }}
        </div>
    </div>
</div>
