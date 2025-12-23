<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('code', __('Código')) }}
            {{ Form::text('code', old('code', $project->code ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 50]) }}
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group">
            {{ Form::label('name', __('Nombre')) }}
            {{ Form::text('name', old('name', $project->name ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 255]) }}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            {{ Form::label('program_id', __('Programa')) }}
            {{ Form::select('program_id', $programs, old('program_id', $project->program_id ?? null), ['class' => 'form-control', 'required' => true, 'placeholder' => __('Seleccione un programa')]) }}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group form-check mt-2">
            {{ Form::hidden('active', 0) }}
            {{ Form::checkbox('active', 1, old('active', $project->active ?? true), ['class' => 'form-check-input', 'id' => 'project-active']) }}
            {{ Form::label('project-active', __('Activo'), ['class' => 'form-check-label ms-2']) }}
        </div>
    </div>
</div>
