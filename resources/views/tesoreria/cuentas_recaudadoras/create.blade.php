{{ Form::open(['route' => 'tesoreria.cuentas-recaudadoras.store', 'class'=>'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('banco', __('Bank'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                <span><i class="ti ti-building-bank"></i></span>
                {{ Form::text('banco', '', ['class' => 'form-control', 'required' => 'required', 'placeholder'=>__('Enter bank name')]) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('numero_cuenta', __('Account Number'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                <span><i class="ti ti-hash"></i></span>
                {{ Form::text('numero_cuenta', '', ['class' => 'form-control', 'required' => 'required', 'placeholder'=>__('Enter account number')]) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tipo', __('Type'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                <span><i class="ti ti-list"></i></span>
                {{ Form::text('tipo', '', ['class' => 'form-control', 'required' => 'required', 'placeholder'=>__('Enter account type')]) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('activo', __('Active'), ['class' => 'form-label']) }}
            <div class="form-check mt-2">
                {{ Form::checkbox('activo', 1, true, ['class' => 'form-check-input', 'id' => 'activo']) }}
                <label class="form-check-label" for="activo">{{ __('Enabled for collections') }}</label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
