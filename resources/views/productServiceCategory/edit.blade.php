@php
    $chatGPT = \App\Models\Utility::settings('enable_chatgpt');
    $enable_chatgpt = !empty($chatGPT);
@endphp
{{ Form::model($category, array('route' => array('product-category.update', $category->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    <div class="row">
        @if ($enable_chatgpt)
        <div>
            <a href="#" data-size="md" data-ajax-popup-over="true" data-url="{{ route('generate', ['category']) }}"
                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}"
                data-title="{{ __('Generate content with AI') }}" class="btn btn-primary btn-sm float-end">
                <i class="ti ti-robot"></i>
                {{__('Generate with AI')}}
            </a>
        </div>
        @endif
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Category Name'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('name', null, ['class' => 'form-control font-style', 'required' => 'required', 'placeholder'=>__('Enter Category Name')]) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('type', __('Category Type'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::select('type', $types, null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12 account {{$category->type =='product & service'? 'd-none':''}}">
            {{Form::label('chart_account_id',__('Account'),['class'=>'form-label'])}}
            <select class="form-control select" name="chart_account" id="chart_account" >
            </select>

        </div>
        <div class="form-group col-md-12 classifier-field {{$category->type =='product & service'? 'd-none':''}}">
            {{ Form::label('objeto_gasto_id', __('Objeto del gasto'), ['class' => 'form-label']) }}
            {{ Form::select('objeto_gasto_id', $objects, null, ['class' => 'form-control select', 'placeholder' => __('Seleccionar')]) }}
        </div>
        <div class="form-group col-md-12 classifier-field {{$category->type =='product & service'? 'd-none':''}}">
            {{ Form::label('fuente_financiamiento_id', __('Fuente de financiamiento'), ['class' => 'form-label']) }}
            {{ Form::select('fuente_financiamiento_id', $fundingSources, null, ['class' => 'form-control select', 'placeholder' => __('Seleccionar')]) }}
        </div>
        <div class="form-group col-md-12 classifier-field {{$category->type =='product & service'? 'd-none':''}}">
            {{ Form::label('programa_id', __('Programa'), ['class' => 'form-label']) }}
            {{ Form::select('programa_id', $programs, null, ['class' => 'form-control select', 'placeholder' => __('Seleccionar')]) }}
        </div>
        <div class="form-group col-md-12 classifier-field {{$category->type =='product & service'? 'd-none':''}}">
            {{ Form::label('proyecto_id', __('Proyecto'), ['class' => 'form-label']) }}
            {{ Form::select('proyecto_id', $projects, null, ['class' => 'form-control select', 'placeholder' => __('Seleccionar')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('color', __('Category Color'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::color('color', null, ['class' => 'form-control jscolor', 'required' => 'required']) }}
            <p class="small">{{ __('For chart representation') }}</p>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script>

    //hide & show chartofaccount

    $(document).on('click', '.cattype', function ()
    {
        toggleBudgetFields($(this).val());
    });

    $(document).on('change', '#type', function () {
        var type = $(this).val();
        toggleBudgetFields(type);

        $.ajax({
            url: '{{route('productServiceCategory.getaccount')}}',
            type: 'POST',
            data: {
                "type": type,
                "_token": "{{ csrf_token() }}",
            },
            success: function (data) {
                $('#chart_account').empty();
                $('#chart_account').append('<option value="">{{__(' --- Select Account ---')}}</option>');
                $.each(data.chart_accounts, function (key, value) {
                    var select = '';
                    if (key == '{{ $category->chart_account_id }}') {
                        select = 'selected';
                    }
                    $('#chart_account').append('<option value="' + key + '"  ' + select + ' class="subAccount">' + value + '</option>');

                    $.each(data.sub_accounts, function (subkey, subvalue) {
                    var select1 = '';

                        if (subvalue.id == '{{ $category->chart_account_id }}') {
                        select1 = 'selected';
                    }
                        if(key == subvalue.account)
                        {
                            $('#chart_account').append('<option value="' + subvalue.id + '"  ' + select1 + '>' + '&nbsp; &nbsp;&nbsp;' + subvalue.name + '</option>');
                        }
                });
                });
            }
        });
    });
    $(document).ready(function (){
        $('#type').trigger('change')
        toggleBudgetFields($('#type').val());
    })

    function toggleBudgetFields(type) {
        if (type != 'product & service') {
            $('.account, .classifier-field').removeClass('d-none').addClass('d-block');
        } else {
            $('.account, .classifier-field').addClass('d-none').removeClass('d-block');
        }
    }
</script>
