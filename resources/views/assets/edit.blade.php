@php
    $chatGPT = \App\Models\Utility::settings('enable_chatgpt');
    $enable_chatgpt = !empty($chatGPT);
@endphp
{{ Form::model($asset, ['route' => ['account-assets.update', $asset->id], 'method' => 'PUT','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        @if ($enable_chatgpt)
            <div>
                <a href="#" data-size="md" data-ajax-popup-over="true" data-url="{{ route('generate', ['assets']) }}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
                    data-title="{{ __('Generate content with AI') }}" class="btn btn-primary btn-sm float-end">
                    <i class="fas fa-robot"></i>
                    {{ __('Generate with AI') }}
                </a>
            </div>
        @endif
         <div class="form-group col-md-6">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder'=>__('Enter Name')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'placeholder'=>__('Enter Amount')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('area', __('Area'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('area', null, ['class' => 'form-control', 'required' => 'required', 'placeholder'=>__('Ingrese el Area')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('code_active', __('Codigo De Activo'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('code_active', null, ['class' => 'form-control', 'required' => 'required', 'placeholder'=>__('Ingrese el Codigo de Activo')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('code_active_category', __('Categoria De Activo'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('code_active_category', null, ['class' => 'form-control', 'required' => 'required', 'placeholder'=>__('Ingrese la Categoria de Codigo de Activo')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('purchase_date', __('Purchase Date'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::date('purchase_date', null, ['class' => 'form-control pc-datepicker-1', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date_garantia', __('Fecha De Garantia'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::date('date_garantia', null, ['class' => 'form-control pc-datepicker-1', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('supported_date', __('Supported Date'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::date('supported_date', null, ['class' => 'form-control pc-datepicker-1', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('proveedor_id', __('Proveedor'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('proveedor_id', $proveedores, null, 
            ['class' => 'form-control select', 'id' => 'proveedor_id', 'data-url' => route('bill.vender'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder'=>__('Enter Description')]) }}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
