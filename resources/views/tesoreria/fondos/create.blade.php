{{ Form::open(['route' => 'tesoreria.fondos.store', 'class'=>'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('nombre', __('Fund Name'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                <span><i class="ti ti-wallet"></i></span>
                {{ Form::text('nombre', '', ['class' => 'form-control', 'required' => 'required', 'placeholder'=>__('Enter fund name')]) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('cuenta_contable_id', __('Chart Of Account'), ['class' => 'form-label']) }}<x-required></x-required>
            <select name="cuenta_contable_id" class="form-control" required="required">
                @foreach ($chartAccounts as $key => $chartAccount)
                    <option value="{{ $key }}" class="subAccount">{{ $chartAccount }}</option>
                    @foreach ($subAccounts as $subAccount)
                        @if ($key == $subAccount['account'])
                            <option value="{{ $subAccount['id'] }}" class="ms-5"> &nbsp; &nbsp;&nbsp;
                                {{ $subAccount['name'] }}</option>
                        @endif
                    @endforeach
                @endforeach
            </select>
            <div class="text-xs mt-1">
                {{ __('Create account here.') }} <a href="{{ route('chart-of-account.index') }}"><b>{{ __('Create account') }}</b></a>
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('monto_inicial', __('Initial Amount'), ['class' => 'form-label']) }}<x-required></x-required>
            <div class="form-icon-user">
                <span><i class="ti ti-cash"></i></span>
                {{ Form::number('monto_inicial', '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0', 'placeholder'=>__('Enter initial amount')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
