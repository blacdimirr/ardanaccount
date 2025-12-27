<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('codigo_nota', __('Código'), ['class' => 'form-label']) }}
            {{ Form::text('codigo_nota', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group">
            {{ Form::label('titulo', __('Título'), ['class' => 'form-label']) }}
            {{ Form::text('titulo', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('periodo', __('Periodo'), ['class' => 'form-label']) }}
            {{ Form::date('periodo', optional($nota->periodo)->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('estado', __('Estado'), ['class' => 'form-label']) }}
            {{ Form::select('estado', [1 => __('Activo'), 0 => __('Inactivo')], $nota->estado ? 1 : 0, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {{ Form::label('contenido', __('Contenido'), ['class' => 'form-label']) }}
            {{ Form::textarea('contenido', null, ['class' => 'form-control', 'rows' => 5, 'required' => 'required']) }}
        </div>
    </div>
</div>
