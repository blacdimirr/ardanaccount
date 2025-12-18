@extends('layouts.admin')
@section('page-title')
    {{ __('Bill Edit') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bill.index') }}">{{ __('Bill') }}</a></li>
    <li class="breadcrumb-item">{{ Auth::user()->billNumberFormat($bill->bill_id) }}</li>
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script>
        var selector = "body";
        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });
            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: true,
                defaultValues: {
                    'status': 1
                },
                show: function () {
                    $(this).slideDown();
                    var file_uploads = $(this).find('input.multi');
                    if (file_uploads.length) {
                        $(this).find('input.multi').MultiFile({
                            max: 3,
                            accept: 'png|jpg|jpeg',
                            max_size: 2048
                        });
                    }
                    JsSearchBox();
                    select2();
                },
                hide: function (deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        var el = $(this);
                        var id = $(el.find('.id')).val();
                        var amount = $(el.find('.amount')).html();
                        var account_id = $(el.find('.account_id')).val();

                        $(".price").change();
                        $(".discount").change();
                        $('.item option').prop('hidden', false);
                        $('.item :selected').each(function () {
                            var ids = $(this).val();
                            if (ids) {
                                $('.item').not(this).find("option[value=" + ids + "]").prop('hidden', true);
                            }
                        });

                        if (id != undefined && id != null && id != '') {
                            $.ajax({
                                url: '{{route('bill.product.destroy')}}',
                                type: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': jQuery('#token').val()
                                },
                                data: {
                                    'id': id,
                                    'amount': amount,
                                    'account_id': account_id,
                                },
                                cache: false,
                                success: function (data) {

                                    $('.item option').prop('hidden', false);
                                    $('.item :selected').each(function () {
                                        var id = $(this).val();
                                        $(".item option[value=" + id + "]").prop("hidden", true);
                                    });

                                    if (data.status) {
                                        show_toastr('success', data.message);
                                    } else {
                                        show_toastr('error', data.message);
                                    }
                                },
                            });
                        }

                        $(this).slideUp(deleteElement);
                        $(this).remove();
                        var inputs = $(".amount");
                        var subTotal = 0;
                        for (var i = 0; i < inputs.length; i++) {
                            subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                        }
                        $('.subTotal').html(subTotal.toFixed(2));
                        $('.totalAmount').html(subTotal.toFixed(2));

                        // NUEVO: recalcular retenciones y pago final
                        recalcRetentionsAndFinal();
                    }

                },
                ready: function (setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });
            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
                for (var i = 0; i < value.length; i++) {
                    var tr = $('#sortable-table .id[value="' + value[i].id + '"]').parent();
                    tr.find('.item').val(value[i].product_id);
                    changeItem(tr.find('.item'));
                }
            }
        }

        $(document).on('change', '#vender', function () {
            $('#vender_detail').removeClass('d-none').addClass('d-block');
            $('#vender-box').removeClass('d-block').addClass('d-none');
            var id = $(this).val();
            var url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: { 'id': id },
                cache: false,
                success: function (data) {
                    if (data != '') {
                        $('#vender_detail').html(data);
                    } else {
                        $('#vender-box').removeClass('d-none').addClass('d-block');
                        $('#vender_detail').removeClass('d-block').addClass('d-none');
                    }
                },
            });
        });

        $(document).on('click', '#remove', function () {
            $('#vender-box').removeClass('d-none').addClass('d-block');
            $('#vender_detail').removeClass('d-block').addClass('d-none');
        });

        $(document).on('change', '.item', function () {
            changeItem($(this));
        });

        var bill_id = '{{$bill->id}}';
        function changeItem(element) {
            var iteams_id = element.val();
            var url = element.data('url');
            // var el = element;
            var row = element.closest('tr');
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: { 'product_id': iteams_id },
                cache: false,
                success: function (data) {
                    var item = JSON.parse(data);

                    $.ajax({
                        url: '{{route('bill.items')}}',
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': jQuery('#token').val()
                        },
                        data: {
                            'bill_id': bill_id,
                            'product_id': iteams_id,
                        },
                        cache: false,
                        success: function (data) {
                            var billItems = JSON.parse(data);

                            console.log(45,billItems);
                            console.log(46,bill_id,iteams_id);
                            if (billItems != null) {
                                var amount = (billItems.price * billItems.quantity);

                                $(row).find('.quantity').val(billItems.quantity);
                                $(row).find('.price').val(billItems.price);
                                $(row).find('.pro_description').val(billItems.description);
                                $(row).find('.discount').val(billItems.discount);
                                // $(el.parent().parent().parent().find('.quantity')).val(billItems.quantity);
                                // $(el.parent().parent().parent().find('.price')).val(billItems.price);
                                // $(el.parent().parent().parent().find('.discount')).val(billItems.discount);
                                // $(el.parent().parent().parent().parent().find('.pro_description')).val(billItems.description);
                                
                            } else {
                                $(row).find('.quantity').val(1);
                                $(row).find('.price').val(item.product.purchase_price);
                                $(row).find('.pro_description').val(item.product.description);
                                $(row).find('.discount').val(0);
                                console.log(47);
                                // $(el.parent().parent().parent().find('.quantity')).val(1);
                                // $(el.parent().parent().parent().find('.price')).val(item.product.purchase_price);
                                // $(el.parent().parent().parent().find('.discount')).val(0);
                                // $(el.parent().parent().parent().parent().find('.pro_description')).val(item.product.description);
                            }

                            var taxes = '';
                            var tax = [];
                            var totalItemTaxRate = 0;
                            for (var i = 0; i < item.taxes.length; i++) {
                                taxes += '<span class="badge bg-primary p-2 px-3 mt-1 me-2">' + item.taxes[i].name + ' ' + '(' + item.taxes[i].rate + '%)' + '</span>';
                                tax.push(item.taxes[i].id);
                                totalItemTaxRate += parseFloat(item.taxes[i].rate);
                            }

                            var discount= $(row).find('.discount').val(0).val();
                            // var discount=$(el.parent().parent().parent().find('.discount')).val();
                            if (billItems != null) {
                                var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((billItems.price * billItems.quantity)- discount);
                            } else {
                                var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((item.product.purchase_price * 1)- discount);
                            }

                            $(row).find('.itemTaxPrice').val(itemTaxPrice.toFixed(2));
                            $(row).find('.itemTaxRate').val(totalItemTaxRate.toFixed(2));
                            $(row).find('.taxes').html(taxes);
                            $(row).find('.tax').val(tax);
                            $(row).find('.unit').html(item.unit);
                            // $(row).find('.discount').val(0);

                            // $(el.parent().parent().parent().find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
                            // $(el.parent().parent().parent().find('.itemTaxRate')).val(totalItemTaxRate.toFixed(2));
                            // $(el.parent().parent().parent().find('.taxes')).html(taxes);
                            // $(el.parent().parent().parent().find('.tax')).val(tax);
                            // $(el.parent().parent().parent().find('.unit')).html(item.unit);

                            var inputs = $(".amount");
                            var subTotal = 0;
                            for (var i = 0; i < inputs.length; i++) {
                                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                            }

                            var accountinputs = $(".accountamount");
                            var accountSubTotal = 0;
                            for (var i = 0; i < accountinputs.length; i++) {
                                var currentInputValue = parseFloat(accountinputs[i].innerHTML);
                                if (!isNaN(currentInputValue)) {
                                    accountSubTotal += currentInputValue;
                                }
                            }

                            var totalItemPrice = 0;
                            var inputs_quantity = $(".quantity");
                            var priceInput = $('.price');
                            var acinputs = $(".accountAmount");
                            for (var j = 0; j < priceInput.length; j++) {
                                if (!isNaN(parseFloat(priceInput[j].value))) {
                                    var accountAmount = parseFloat(acinputs[j].value);
                                    if (isNaN(accountAmount)) accountAmount = 0;
                                    var itemTotal = (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value) + accountAmount);
                                    totalItemPrice += itemTotal;
                                }
                            }

                            var totalItemTaxPrice = 0;
                            var itemTaxPriceInput = $('.itemTaxPrice');
                            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                                if (!isNaN(parseFloat(itemTaxPriceInput[j].value))) {
                                    totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
                                    if (billItems != null) {
                                        $(row).find('.amount').html(parseFloat(amount)+parseFloat(itemTaxPrice)-parseFloat(discount));
                                        // $(el.parent().parent().parent().find('.amount')).html(parseFloat(amount)+parseFloat(itemTaxPrice)-parseFloat(discount));
                                    } else {
                                        $(row).find('.amount').html(parseFloat(item.totalAmount)+parseFloat(itemTaxPrice));
                                        // $(el.parent().parent().parent().find('.amount')).html(parseFloat(item.totalAmount)+parseFloat(itemTaxPrice));
                                    }
                                }
                            }

                            var totalItemDiscountPrice = 0;
                            var itemDiscountPriceInput = $('.discount');
                            for (var k = 0; k < itemDiscountPriceInput.length; k++) {
                                if (!isNaN(parseFloat(itemDiscountPriceInput[k].value))) {
                                    totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
                                }
                            }

                            $('.subTotal').html(totalItemPrice.toFixed(2));
                            $('.totalTax').html(totalItemTaxPrice.toFixed(2));
                            $('.totalAmount').html((parseFloat(totalItemPrice) - parseFloat(totalItemDiscountPrice) + parseFloat(totalItemTaxPrice)).toFixed(2));
                            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));

                            // NUEVO: recalcular retenciones y pago final
                            recalcRetentionsAndFinal();
                        }
                    });
                },
            });
        }

        $(document).on('keyup change', '.quantity', function () {
            var el = $(this).parent().parent().parent().parent();
            var quantity = $(this).val();
            var price = $(el.find('.price')).val();
            var discount = $(el.find('.discount')).val();
            if(discount.length <= 0) { discount = 0; }

            var totalItemPrice = (quantity * price) - discount;
            var amount = (totalItemPrice);

            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
            $(el.find('.amount')).html(parseFloat(itemTaxPrice)+parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }

            var totalInputItemPrice = 0;
            var inputs_quantity = $(".quantity");
            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalInputItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var totalAccount = 0;
            var accountInput = $('.accountAmount');
            for (var j = 0; j < accountInput.length; j++) {
                var accountInputPrice = accountInput[j].value!='' ? accountInput[j].value : 0;
                totalAccount += (parseFloat(accountInputPrice));
            }

            var inputs = $(".amount");
            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            var sumAmount = totalInputItemPrice + totalAccount;
            $('.subTotal').html(sumAmount.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));
            $('.totalAmount').html((parseFloat(subTotal)+totalAccount).toFixed(2));

            // NUEVO
            recalcRetentionsAndFinal();
        });

        $(document).on('keyup change', '.price', function () {
            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var quantity = $(el.find('.quantity')).val();
            var discount = $(el.find('.discount')).val();
            if(discount.length <= 0) { discount = 0; }

            var totalItemPrice = (quantity * price)-discount;
            var amount = (totalItemPrice);

            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
            $(el.find('.amount')).html(parseFloat(itemTaxPrice)+parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }

            var totalItemPriceSum = 0;
            var inputs_quantity = $(".quantity");
            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPriceSum += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var totalAccount = 0;
            var accountInput = $('.accountAmount');
            for (var j = 0; j < accountInput.length; j++) {
                var accountInputPrice = accountInput[j].value!='' ? accountInput[j].value : 0;
                totalAccount += (parseFloat(accountInputPrice));
            }

            var inputs = $(".amount");
            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            var sumAmount = totalItemPriceSum + totalAccount;
            $('.subTotal').html(sumAmount.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));
            $('.totalAmount').html((parseFloat(subTotal) + totalAccount).toFixed(2));

            // NUEVO
            recalcRetentionsAndFinal();
        });

        $(document).on('keyup change', '.discount', function () {
            var el = $(this).parent().parent().parent();
            var discount = $(this).val();
            if(discount.length <= 0) { discount = 0; }
            var price = $(el.find('.price')).val();
            var quantity = $(el.find('.quantity')).val();

            var totalItemPrice = (quantity * price) - discount;
            var amount = (totalItemPrice);

            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
            $(el.find('.amount')).html(parseFloat(itemTaxPrice)+parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }

            var totalItemPriceSum = 0;
            var inputs_quantity = $(".quantity");
            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPriceSum += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");
            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            var totalItemDiscountPrice = 0;
            var itemDiscountPriceInput = $('.discount');
            for (var k = 0; k < itemDiscountPriceInput.length; k++) {
                if (!isNaN(parseFloat(itemDiscountPriceInput[k].value))) {
                    totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
                }
            }

            var totalAccount = 0;
            var accountInput = $('.accountAmount');
            for (var j = 0; j < accountInput.length; j++) {
                var accountInputPrice = accountInput[j].value!='' ? accountInput[j].value : 0;
                totalAccount += (parseFloat(accountInputPrice));
            }

            var sumAmount = totalItemPriceSum + totalAccount;
            $('.subTotal').html(sumAmount.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));
            $('.totalAmount').html((parseFloat(subTotal) + totalAccount).toFixed(2));
            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));

            // NUEVO
            recalcRetentionsAndFinal();
        });

        $(document).on('keyup change', '.accountAmount', function () {
            var el1 = $(this).parent().parent().parent().parent();
            var el = $(this).parent().parent().parent().parent().parent();

            var quantityDiv = $(el.find('.quantity'));
            var priceDiv = $(el.find('.price'));
            var discountDiv = $(el.find('.discount'));

            var itemSubTotal=0;
            var itemSubTotalDiscount=0;
            for (var p = 0; p < priceDiv.length; p++) {
                var quantity=quantityDiv[p].value;
                var price=priceDiv[p].value;
                var discount=discountDiv[p].value;
                if(discount.length <= 0) { discount = 0; }
                itemSubTotal += (quantity*price);
                itemSubTotalDiscount += (quantity*price) - (discount);
            }

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                var parsedValue = parseFloat(itemTaxPriceInput[j].value);
                if (!isNaN(parsedValue)) {
                    totalItemTaxPrice += parsedValue;
                }
            }

            var amount = $(this).val();
            el1.find('.accountamount').html(amount);
            var totalAccount = 0;
            var accountInput = $('.accountAmount');
            for (var j = 0; j < accountInput.length; j++) {
                totalAccount += (parseFloat(accountInput[j].value) );
            }

            var inputs = $(".accountamount");
            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').text((totalAccount+itemSubTotal).toFixed(2));
            $('.totalAmount').text((parseFloat((subTotal + itemSubTotalDiscount) + (totalItemTaxPrice))).toFixed(2));

            // NUEVO
            recalcRetentionsAndFinal();
        });

        $(document).on('click', '[data-repeater-create]', function () {
            $('.item option').prop('hidden', false);
            $('.item :selected').each(function () {
                var id = $(this).val();
                $(".item option[value=" + id + "]").prop("hidden", true);
            });
            // NUEVO
            setTimeout(recalcRetentionsAndFinal, 100);
        });

        // Disparador inicial
        // $('.accountAmount').trigger('keyup');
    </script>

    <!-- NUEVO: cálculo de retenciones y pago final -->
    <script>
        // Calcula:
        //  - Retención 5% sobre neto (subTotal)
        //  - Retención 30% sobre ITBIS (totalTax)
        //  - Total con impuestos (subTotal + totalTax)
        //  - Pago final = (subTotal + totalTax) - ret5 - ret30
        function recalcRetentionsAndFinal() {
            var sub = parseFloat(($('.subTotal').text() || "0").replace(/,/g,'')) || 0; // Neto sin ITBIS
            var tax = parseFloat(($('.totalTax').text() || "0").replace(/,/g,'')) || 0; // ITBIS total
            var grossWithTax = sub + tax;

            var ret5  = +(sub * 0.05).toFixed(2);   // 5% sobre neto
            var ret30 = +(tax * 0.30).toFixed(2);   // 30% sobre ITBIS

            var finalPayable = +(grossWithTax - ret5 - ret30).toFixed(2);

            // Pinta (si quieres signo menos visual: '–' + valor)
            $('.retentionNet').text(ret5.toFixed(2));
            $('.retentionIva').text(ret30.toFixed(2));
            $('.totalAmount').text(grossWithTax.toFixed(2));
            $('.finalPayable').text(finalPayable.toFixed(2));

            // Inputs ocultos para backend
            $('.retencion5Input').val(ret5.toFixed(2));
            $('.retencion30Input').val(ret30.toFixed(2));
            $('.finalPayableInput').val(finalPayable.toFixed(2));
        }

        // Listeners globales por si algo externo cambia
        $(document).on('keyup change', '.quantity, .price, .discount, .accountAmount, .item', function() {
            setTimeout(recalcRetentionsAndFinal, 50);
        });

        // Cálculo inicial
        $(function () { recalcRetentionsAndFinal(); });
    </script>

    <script>
        JsSearchBox();
    </script>
    <script>
        setTimeout(() => {
            $('#due_date').trigger('click');
        }, 1500);
    </script>
@endpush

@section('content')
    <div class="row">
        {{ Form::model($bill, ['route' => ['bill.update', $bill->id], 'method' => 'PUT', 'class' => 'w-100 needs-validation','novalidate']) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="vender-box">
                                {{ Form::label('vender_id', __('Vendor'), ['class' => 'form-label']) }}<x-required></x-required>
                                {{ Form::select('vender_id', $venders, null, ['class' => 'form-control select2', 'id' => 'vender', 'data-url' => route('bill.vender'), 'required' => 'required']) }}
                                <div class="text-xs mt-1">
                                    {{ __('Create vendor here.') }} <a href="{{ route('vender.index') }}"><b>{{ __('Create vendor') }}</b></a>
                                </div>
                            </div>
                            <div id="vender_detail" class="d-none">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('bill_date', __('Bill Date'), ['class' => 'form-label']) }}<x-required></x-required>
                                        <div class="form-icon-user">
                                            {{ Form::date('bill_date', null, ['class' => 'form-control', 'required' => 'required']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<x-required></x-required>
                                        <div class="form-icon-user">
                                            {{ Form::date('due_date', null, ['class' => 'form-control', 'required' => 'required']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('bill_number', __('Bill Number'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user">
                                            <input type="text" class="form-control" value="{{ $bill_number }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('order_number', __('Order Number'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user">
                                            {{ Form::text('order_number', null, ['class' => 'form-control', 'placeholder'=>__('Enter Order Number')]) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group" id="estatus-box">
                                    {{ Form::label('estatus_id', __('Estatus'), ['class' => 'form-label']) }}
                                    {{ Form::select('estatus_id', $estatus, $bill->status, ['class' => 'form-control select', 'id' => 'estatus', 'data-url' => route('bill.estatus_bills'), 'required' => 'required']) }}
                                  </div>
                                  <div id="estatus_detail" class="d-none"></div>
                                </div>

                                @if (!$customFields->isEmpty())
                                    <div class="col-md-6">
                                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                            @include('customFields.formBuilder')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="col-12">
            <h5 class=" d-inline-block mb-4">{{ __('Product & Services') }}</h5>
            <div class="card repeater" data-value='{{ json_encode($items) }}'>
                <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="javascript:void(0)" data-repeater-create="" class="btn btn-primary" data-bs-toggle="modal"
                                   data-target="#add-bank">
                                    <i class="ti ti-plus"></i> {{ __('Add item') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0" data-repeater-list="items" id="sortable-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Items') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Discount') }}</th>
                                    <th>{{ __('Tax') }} (%)</th>
                                    <th class="text-end">
                                        {{ __('Amount') }} <br>
                                        <small class="text-danger font-weight-bold">{{ __('after tax & discount') }}</small>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                                <tr>
                                    {{ Form::hidden('id',null, ['class' => 'form-control id']) }}
                                    {{ Form::hidden('account_id',null, ['class' => 'form-control account_id']) }}
                                    <td width="25%">
                                        <div class="form-group flex-nowrap">
                                            {{ Form::select('items', $product_services, null, ['class' => 'form-control select2 item', 'data-url' => route('bill.product')]) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form flex-nowrap">
                                            {{ Form::number('quantity', null, ['class' => 'form-control quantity', 'required' => 'required', 'placeholder' => __('Qty')]) }}
                                            <span class="unit input-group-text bg-transparent"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form flex-nowrap">
                                            {{ Form::number('price', null, ['min' => '0.00','step'=>'.01','class' => 'form-control price', 'required' => 'required', 'placeholder' => __('Price')]) }}
                                            <span class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form flex-nowrap">
                                            {{ Form::number('discount', null, ['class' => 'form-control discount', 'required' => 'required', 'placeholder' => __('Discount')]) }}
                                            <span class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <div class="input-group flex-nowrap">
                                                <div class="taxes"></div>
                                                {{ Form::hidden('tax', '', ['class' => 'form-control tax']) }}
                                                {{ Form::hidden('itemTaxPrice', '', ['class' => 'form-control itemTaxPrice']) }}
                                                {{ Form::hidden('itemTaxRate', '', ['class' => 'form-control itemTaxRate']) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end amount">0.00</td>
                                    <td>
                                        @can('delete proposal product')
                                            <div class="action-btn ms-2 float-end mb-3" data-repeater-delete>
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center m-2 p-2 bg-danger"
                                                   data-bs-toggle="tooltip" data-bs-original-title="{{__('Delete')}}" title="{{__('Delete')}}">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>

                                <tr>
                                    <td >
                                        <div class="form-group ">
                                            <select name="chart_account_id" class="form-control select2">
                                                @foreach ($chartAccounts as $key => $chartAccount)
                                                    <option value="{{ $key }}" class="subAccount">{{ $chartAccount}}</option>
                                                    {{-- @foreach ($subAccounts as $subAccount) --}}
                                                    {{-- @if ($key == $subAccount['account_id']) --}}
                                                        {{-- <option value="{{ $subAccount['id'] }}" class="ms-5"> &nbsp;&nbsp;&nbsp; {{ $subAccount['name'] }}</option> --}}
                                                    {{-- @endif --}}
                                                    {{-- @if ($key == $subAccount['account']) --}}
                                                    {{-- <option value="{{ $subAccount['id'] }}" class="ms-5"> &nbsp;&nbsp;&nbsp; {{ $subAccount['name'] }}</option> --}}
                                                    {{-- @endif --}}
                                                    {{-- @endforeach --}}
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>

                                    <td >
                                        <div class="form-group input-group search-form flex-nowrap">
                                            {{ Form::number('amount',null, ['min' => '0.00','step'=>'.1','class' => 'form-control accountAmount','placeholder'=>__('Amount')]) }}
                                            <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                                        </div>
                                    </td>

                                    <td colspan="2" >
                                        <div class="form-group input-group search-form flex-nowrap">
                                            {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'1','placeholder'=>__('Description')]) }}
                                        </div>
                                    </td>
                                    <td></td>
                                    <td class="text-end accountamount">0.00</td>
                                </tr>

                                <tr>
                                    <td width="25%" class="form-group pt-0 flex-nowrap">
                                        <div class="form-group">
                                            {{ Form::label('category_id', __('Category'), ['class' => 'form-label']) }}<x-required></x-required>
                                            {{ Form::select('category_id', $category, $bill->category_id, ['class' => 'form-control select2', 'required' => 'required']) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- NUEVO TFOOT COMPLETO CON RETENCIONES Y PAGO FINAL -->
                            <tfoot>
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                    <td><strong>{{ __('Sub Total') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end subTotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                    <td><strong>{{ __('Discount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalDiscount">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                    <td><strong>{{ __('Tax') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr>

                                <!-- NUEVO: Retención 5% sobre neto -->
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                    <td class="text-danger">
                                        <strong>{{ __('Retención 5% (sobre neto)') }} ({{ \Auth::user()->currencySymbol() }})</strong>
                                    </td>
                                    <td class="text-end retentionNet">0.00</td>
                                    <td></td>
                                </tr>

                                <!-- NUEVO: Retención 30% sobre ITBIS -->
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td>
                                    <td class="text-danger">
                                        <strong>{{ __('Retención 30% (sobre ITBIS)') }} ({{ \Auth::user()->currencySymbol() }})</strong>
                                    </td>
                                    <td class="text-end retentionIva">0.00</td>
                                    <td></td>
                                </tr>

                                <!-- Total con impuestos (referencia) -->
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    <td class="blue-text"><strong>{{ __('Total con impuestos') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="blue-text text-end totalAmount">0.00</td>
                                    <td></td>
                                </tr>

                                <!-- NUEVO: Pago final al proveedor -->
                                <tr>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    <td class="blue-text"><strong>{{ __('Pago final al proveedor') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="blue-text text-end finalPayable">0.00</td>
                                    <td></td>
                                </tr>

                                <!-- Inputs ocultos para backend -->
                                <tr class="d-none">
                                    <td colspan="7">
                                        <input type="hidden" name="retencion_5" class="retencion5Input" value="0">
                                        <input type="hidden" name="retencion_30_itbis" class="retencion30Input" value="0">
                                        <input type="hidden" name="pago_final_proveedor" class="finalPayableInput" value="0">
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('bill.index') }}';" class="btn btn-light me-2">
            <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection
