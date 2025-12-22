<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('supplier_type', __('Tipo de sujeto')) }}
            {{ Form::text('supplier_type', old('supplier_type', $retentionRule->supplier_type ?? null), ['class' => 'form-control', 'placeholder' => __('Ej: suplidor, profesional, empresa')]) }}
            <small class="text-muted">{{ __('Este valor se compara con el campo "Tipo de suplidor" en las facturas.') }}</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('service_category_id', __('Servicio / categoría')) }}
            {{ Form::select('service_category_id', $categories, old('service_category_id', $retentionRule->service_category_id ?? ''), ['class' => 'form-control', 'placeholder' => __('Aplica a todas las categorías')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {{ Form::label('itbis_retention_rate', __('% ITBIS retenido')) }}
            {{ Form::number('itbis_retention_rate', old('itbis_retention_rate', $retentionRule->itbis_retention_rate ?? 0), ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'max' => 100, 'required' => true]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {{ Form::label('isr_retention_rate', __('% ISR retenido')) }}
            {{ Form::number('isr_retention_rate', old('isr_retention_rate', $retentionRule->isr_retention_rate ?? 0), ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'max' => 100, 'required' => true]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {{ Form::label('government_retention_rate', __('% Retención gubernamental')) }}
            {{ Form::number('government_retention_rate', old('government_retention_rate', $retentionRule->government_retention_rate ?? 0), ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'max' => 100, 'required' => true]) }}
        </div>
    </div>
    <div class="col-md-3 d-flex align-items-center">
        <div class="form-group form-check mt-4">
            {{ Form::hidden('active', 0) }}
            {{ Form::checkbox('active', 1, old('active', $retentionRule->active ?? true), ['class' => 'form-check-input', 'id' => 'active']) }}
            {{ Form::label('active', __('Regla activa'), ['class' => 'form-check-label ms-2']) }}
        </div>
    </div>
</div>
