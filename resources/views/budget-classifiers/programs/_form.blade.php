<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('code', __('Código')) }}
            {{ Form::text('code', old('code', $program->code ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 50]) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('name', __('Nombre')) }}
            {{ Form::text('name', old('name', $program->name ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 255]) }}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group form-check mt-2">
            {{ Form::hidden('active', 0) }}
            {{ Form::checkbox('active', 1, old('active', $program->active ?? true), ['class' => 'form-check-input', 'id' => 'program-active']) }}
            {{ Form::label('program-active', __('Activo'), ['class' => 'form-check-label ms-2']) }}
        </div>
    </div>
</div>
