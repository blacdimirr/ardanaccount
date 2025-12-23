<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('code', __('Código')) }}
            {{ Form::text('code', old('code', $fundingSource->code ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 50]) }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('description', __('Descripción')) }}
            {{ Form::text('description', old('description', $fundingSource->description ?? null), ['class' => 'form-control', 'required' => true, 'maxlength' => 255]) }}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group form-check mt-2">
            {{ Form::hidden('active', 0) }}
            {{ Form::checkbox('active', 1, old('active', $fundingSource->active ?? true), ['class' => 'form-check-input', 'id' => 'funding-active']) }}
            {{ Form::label('funding-active', __('Activo'), ['class' => 'form-check-label ms-2']) }}
        </div>
    </div>
</div>
