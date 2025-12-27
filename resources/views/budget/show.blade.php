@extends('layouts.admin')
@section('page-title')
    {{__('Budget Vs Actual : ')}}{{ $budget->name }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('budget.index')}}">{{__('Budget Planner')}}</a></li>
    <li class="breadcrumb-item">{{ $budget->name }}</li>
@endsection

@push('script-page')
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script>
        //Income Total
        $(document).on('keyup', '.income_data', function () {
            //category wise total
            var el = $(this).parent().parent();
            var inputs = $(el.find('.income_data'));

            var totalincome = 0;
            for (var i = 0; i < inputs.length; i++) {
                var price = $(inputs[i]).val();
                totalincome = parseFloat(totalincome) + parseFloat(price);
            }
            el.find('.totalIncome').html(totalincome);

            // month wise total //
            var month_income = $(this).data('month');
            var month_inputs = $(el.parent().find('.' + month_income + '_income'));
            var month_totalincome = 0;
            for (var i = 0; i < month_inputs.length; i++) {
                var month_price = $(month_inputs[i]).val();
                month_totalincome = parseFloat(month_totalincome) + parseFloat(month_price);
            }
            var month_total_income = month_income + '_total_income';
            el.parent().find('.' + month_total_income).html(month_totalincome);

            //all total //
            var total_inputs = $(el.parent().find('.totalIncome'));
            var income = 0;
            for (var i = 0; i < total_inputs.length; i++) {
                var price = $(total_inputs[i]).html();
                income = parseFloat(income) + parseFloat(price);
            }
            el.parent().find('.income').html(income);
        });

        //Expense Total
        $(document).on('keyup', '.expense_data', function () {
            //category wise total
            var el = $(this).parent().parent();
            var inputs = $(el.find('.expense_data'));

            var totalexpense = 0;
            for (var i = 0; i < inputs.length; i++) {
                var price = $(inputs[i]).val();
                totalexpense = parseFloat(totalexpense) + parseFloat(price);
            }
            el.find('.totalExpense').html(totalexpense);

            // month wise total //
            var month_expense = $(this).data('month');
            var month_inputs = $(el.parent().find('.' + month_expense + '_expense'));
            var month_totalexpense = 0;
            for (var i = 0; i < month_inputs.length; i++) {
                var month_price = $(month_inputs[i]).val();
                month_totalexpense = parseFloat(month_totalexpense) + parseFloat(month_price);
            }
            var month_total_expense = month_expense + '_total_expense';
            el.parent().find('.' + month_total_expense).html(month_totalexpense);

            //all total //
            var total_inputs = $(el.parent().find('.totalExpense'));
            var expense = 0;
            for (var i = 0; i < total_inputs.length; i++) {
                var price = $(total_inputs[i]).html();
                expense = parseFloat(expense) + parseFloat(price);
            }
            el.parent().find('.expense').html(expense);
        });

        //Hide & Show
        $(document).on('change', '.period', function () {
            var period = $(this).val();

            $('.budget_plan').removeClass('d-block');
            $('.budget_plan').addClass('d-none');
            $('#' + period).removeClass('d-none');
            $('#' + period).addClass('d-block');
        });

        // trigger
        $('.period').trigger('change');
    </script>
@endpush

@section('action-btn')
@endsection

<style type="text/css">
.custom_temp{
    overflow-x: scroll;
}
</style>

@section('content')
<div class="col-12">
    <div class="card p-4 my-4">
        <h6 class="report-text text-center mb-0">{{__('Year :')}} {{ $budget->from }}</h6>
    </div>
</div>
<div class="col-12">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Budget overview (PIA / PIM)') }}</h5>
        </div>
        <div class="card-body table-border-style">
            <div class="table-responsive">
                @php
                    $summarySections = [
                        'income' => [
                            'label' => __('Income'),
                            'products' => $incomeproduct,
                            'executed' => $executedIncomeTotals ?? [],
                        ],
                        'expense' => [
                            'label' => __('Expense'),
                            'products' => $expenseproduct,
                            'executed' => $executedExpenseTotals ?? [],
                        ],
                    ];
                    $pimModalData = [];
                @endphp
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('PIA') }}</th>
                        <th>{{ __('PIM') }}</th>
                        <th>{{ __('Committed') }}</th>
                        <th>{{ __('Accrued') }}</th>
                        <th>{{ __('Paid') }}</th>
                        <th>{{ __('Available Balance') }}</th>
                        @if($canEditPim)
                            <th class="text-end" width="10%">{{ __('Action') }}</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($summarySections as $type => $section)
                        <tr>
                            <th colspan="{{ $canEditPim ? 8 : 7 }}" class="text-dark light_blue">
                                <span>{{ $section['label'] }}</span>
                            </th>
                        </tr>
                        @foreach($section['products'] as $productService)
                            @php
                                $pia = data_get($piaTotals, $type.'.'.$productService->id, 0);
                                $pim = data_get($pimTotals, $type.'.'.$productService->id, $pia);
                                $committed = data_get($committedTotals, $type.'.'.$productService->id, 0);
                                $accrued = data_get($accruedTotals, $type.'.'.$productService->id, 0);
                                $paid = data_get($paidTotals, $type.'.'.$productService->id, 0);
                                $available = data_get($availableTotals, $type.'.'.$productService->id, 0);
                                $executed = $accrued;
                                $modalId = 'pimModal-'.$type.'-'.$productService->id;

                                if($canEditPim){
                                    $pimModalData[] = [
                                        'id' => $modalId,
                                        'name' => $productService->name,
                                        'pim' => $pim,
                                        'executed' => $executed,
                                        'category_id' => $productService->id,
                                        'type' => $type,
                                    ];
                                }
                            @endphp
                            <tr>
                                <td class="text-dark">{{ $productService->name }}</td>
                                <td class="text-dark">{{ \Auth::user()->priceFormat($pia) }}</td>
                                <td class="text-dark">{{ \Auth::user()->priceFormat($pim) }}</td>
                                <td class="text-dark">{{ \Auth::user()->priceFormat($committed) }}</td>
                                <td class="text-dark">{{ \Auth::user()->priceFormat($accrued) }}</td>
                                <td class="text-dark">{{ \Auth::user()->priceFormat($paid) }}</td>
                                <td class="text-dark">{{ \Auth::user()->priceFormat($available) }}</td>
                                @if($canEditPim)
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($canEditPim)
            @foreach($pimModalData as $modal)
                <div class="modal fade" id="{{ $modal['id'] }}" tabindex="-1" aria-labelledby="{{ $modal['id'] }}Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            {{ Form::open(['route' => ['budget.pim.update', $budget->id], 'method' => 'POST', 'data-loading' => true, 'data-loading-text' => __('Actualizando presupuesto...'), 'data-loading-message' => __('Estamos procesando su solicitud. Este proceso puede tardar unos segundos. Por favor, no cierre la página ni presione atrás.')]) }}
                            <div class="modal-header">
                                <h5 class="modal-title" id="{{ $modal['id'] }}Label">{{ __('Update PIM') }} - {{ $modal['name'] }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                {{ Form::hidden('category_id', $modal['category_id']) }}
                                {{ Form::hidden('type', $modal['type']) }}
                                <div class="mb-3">
                                    {{ Form::label('monto_pim', __('PIM'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {{ Form::number('monto_pim', $modal['pim'], ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'required' => true]) }}
                                </div>
                                <div class="mb-3">
                                    {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}<x-required></x-required>
                                    {{ Form::textarea('reason', null, ['class' => 'form-control', 'rows' => 3, 'required' => true]) }}
                                </div>
                                <p class="text-muted mb-0">{{ __('Executed amount: :value', ['value' => \Auth::user()->priceFormat($modal['executed'])]) }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-body table-border-style custom_temp">

{{--  Monthly Budget--}}
@if($budget->period == 'monthly')
    <table class="table table-bordered table-item data">
        <thead>
        <tr>
            <th rowspan="2">{{ __('Category') }}</th>
            <th rowspan="2">{{ __('Objeto del gasto') }}</th>
            <th rowspan="2">{{ __('Programa') }}</th>
            <th rowspan="2">{{ __('Proyecto') }}</th>
            @foreach($monthList as $month)
                <th colspan="3" scope="colgroup" class="text-center br-1px">{{__($month)}}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($monthList as $month)
                <th scope="col" class="br-1px">Budget</th>
                <th scope="col" class="br-1px">Actual</th>
                <th scope="col" class="br-1px">Over Budget</th>
            @endforeach
        </tr>
        </thead>

        <!----INCOME Category ---------------------->
        <tr>
            <th colspan="{{ count($monthList) * 3 + 4 }}" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
        </tr>

        @php $overBudgetTotal = []; @endphp

        @foreach ($incomeproduct as $productService)
            <tr>
                <td class="text-dark">{{ $productService->name }}</td>
                <td class="text-dark">{{ optional($productService->objetoGasto)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->programa)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->proyecto)->code ?? '-' }}</td>
                @foreach($monthList as $month)
                    @php
                        $budgetAmount = data_get($budget['income_data'], $productService->id.'.'.$month, 0);
                        $actualAmount = data_get($incomeArr, $productService->id.'.'.$month, 0);
                        $overBudgetAmount = $actualAmount - $budgetAmount;
                        $overBudgetTotal[$productService->id][$month] = $overBudgetAmount;

                        $pctBA = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                        $pctOB = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $overBudgetAmount) : 0;
                    @endphp

                    <td class="income_data {{$month}}_income">{{ \Auth::user()->priceFormat($budgetAmount) }}</td>
                    <td>
                        {{ \Auth::user()->priceFormat($actualAmount) }}
                        <p>{{ $pctBA ? '('.$pctBA.'%)' : '' }}</p>
                    </td>
                    <td>
                        {{ \Auth::user()->priceFormat($overBudgetAmount) }}
                        <p class="{{ $budgetAmount < $overBudgetAmount ? 'green-text' : '' }} {{ $budgetAmount > $overBudgetAmount ? 'red-text' : '' }}">{{ $pctOB ? '('.$pctOB.'%)' : '' }}</p>
                    </td>
                @endforeach
            </tr>
        @endforeach

        @php
            $overBudgetTotalArr = [];
            foreach($overBudgetTotal as $overBudget){
                foreach($overBudget as $k => $value){
                    $overBudgetTotalArr[$k] = ($overBudgetTotalArr[$k] ?? 0) + $value;
                }
            }
        @endphp

        <tr class="total">
            <td class="text-dark" colspan="4"><strong>{{__('Total :')}}</strong></td>
            @foreach($monthList as $month)
                @php
                    $bt = $budgetTotal[$month] ?? 0;
                    $it = $incomeTotalArr[$month] ?? 0;
                    $ob = $overBudgetTotalArr[$month] ?? 0;
                    $pctTI  = $bt ? \App\Models\Budget::percentage($bt, $it) : 0;
                    $pctTOB = $bt ? \App\Models\Budget::percentage($bt, $ob) : 0;
                @endphp
                <td class="text-dark {{$month}}_total_income"><strong>{{ \Auth::user()->priceFormat($bt) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($it) }}</strong>
                    <p>{{ $pctTI ? '('.$pctTI.'%)' : '' }}</p>
                </td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($ob) }}</strong>
                    <p class="{{ $bt < $ob ? 'green-text' : '' }} {{ $bt > $ob ? 'red-text' : '' }}">{{ $pctTOB ? '('.$pctTOB.'%)' : '' }}</p>
                </td>
            @endforeach
        </tr>

        <!------------ EXPENSE Category ---------------------->
        <tr>
            <th colspan="{{ count($monthList) * 3 + 4 }}" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
        </tr>

        @php $overExpenseBudgetTotal = []; @endphp

        @foreach ($expenseproduct as $productService)
            <tr>
                <td class="text-dark">{{ $productService->name }}</td>
                <td class="text-dark">{{ optional($productService->objetoGasto)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->programa)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->proyecto)->code ?? '-' }}</td>
                @foreach($monthList as $month)
                    @php
                        $budgetAmount = data_get($budget['expense_data'], $productService->id.'.'.$month, 0);
                        $actualAmount = data_get($expenseArr, $productService->id.'.'.$month, 0);
                        $overBudgetAmount = $actualAmount - $budgetAmount;
                        $overExpenseBudgetTotal[$productService->id][$month] = $overBudgetAmount;

                        $pctBA = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                        $pctOB = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $overBudgetAmount) : 0;
                    @endphp

                    <td class="expense_data {{$month}}_expense">{{ \Auth::user()->priceFormat($budgetAmount) }}</td>
                    <td>
                        {{ \Auth::user()->priceFormat($actualAmount) }}
                        <p>{{ $pctBA ? '('.$pctBA.'%)' : '' }}</p>
                    </td>
                    <td>
                        {{ \Auth::user()->priceFormat($overBudgetAmount) }}
                        <p class="{{ $budgetAmount < $overBudgetAmount ? 'green-text' : '' }} {{ $budgetAmount > $overBudgetAmount ? 'red-text' : '' }}">{{ $pctOB ? '('.$pctOB.'%)' : '' }}</p>
                    </td>
                @endforeach
            </tr>
        @endforeach

        @php
            $overExpenseBudgetTotalArr = [];
            foreach($overExpenseBudgetTotal as $overExpenseBudget){
                foreach($overExpenseBudget as $k => $value){
                    $overExpenseBudgetTotalArr[$k] = ($overExpenseBudgetTotalArr[$k] ?? 0) + $value;
                }
            }
        @endphp

        <tr class="total">
            <td class="text-dark" colspan="4"><strong>{{__('Total :')}}</strong></td>
            @foreach($monthList as $month)
                @php
                    $be = $budgetExpenseTotal[$month] ?? 0;
                    $et = $expenseTotalArr[$month] ?? 0;
                    $oeb = $overExpenseBudgetTotalArr[$month] ?? 0;
                    $pctTE   = $be ? \App\Models\Budget::percentage($be, $et) : 0;
                    $pctTOEB = $be ? \App\Models\Budget::percentage($be, $oeb) : 0;
                @endphp
                <td class="text-dark {{$month}}_total_expense"><strong>{{ \Auth::user()->priceFormat($be) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($et) }}</strong>
                    <p>{{ $pctTE ? '('.$pctTE.'%)' : '' }}</p>
                </td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($oeb) }}</strong>
                    <p class="{{ $be < $oeb ? 'green-text' : '' }} {{ $be > $oeb ? 'red-text' : '' }}">{{ $pctTOEB ? '('.$pctTOEB.'%)' : '' }}</p>
                </td>
            @endforeach
        </tr>

        <tfoot>
        <tr class="total" style="background:#f8f9fd;">
            <td class="text-dark" colspan="4"><strong>{{__('NET PROFIT :')}}</strong></td>
            @php
                $overbudgetprofit = [];
                foreach(array_unique(array_merge(array_keys($overBudgetTotalArr ?? []), array_keys($overExpenseBudgetTotalArr ?? []))) as $v){
                    $ob = $overBudgetTotalArr[$v] ?? 0;
                    $oe = $overExpenseBudgetTotalArr[$v] ?? 0;
                    $overbudgetprofit[$v] = $ob - $oe;
                }
            @endphp

            @foreach($monthList as $month)
                @php
                    $bp = $budgetprofit[$month] ?? 0;
                    $ap = $actualprofit[$month] ?? 0;
                    $obp = $overbudgetprofit[$month] ?? 0;
                    $pctP   = $bp ? \App\Models\Budget::percentage($bp, $ap) : 0;
                    $pctPOB = $bp ? \App\Models\Budget::percentage($bp, $obp) : 0;
                @endphp
                <td class="text-dark"><strong>{{ \Auth::user()->priceFormat($bp) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($ap) }}</strong>
                    <p>{{ $pctP ? '('.$pctP.'%)' : '' }}</p>
                </td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($obp) }}</strong>
                    <p class="{{ $bp < $obp ? 'green-text' : '' }} {{ $bp > $obp ? 'red-text' : '' }}">{{ $pctPOB ? '('.$pctPOB.'%)' : '' }}</p>
                </td>
            @endforeach
        </tr>
        </tfoot>
    </table>

{{--  Quarterly Budget--}}
@elseif($budget->period == 'quarterly')
    <table class="table table-bordered table-item data">
        <thead>
        <tr>
            <th rowspan="2">{{ __('Category') }}</th>
            <th rowspan="2">{{ __('Objeto del gasto') }}</th>
            <th rowspan="2">{{ __('Programa') }}</th>
            <th rowspan="2">{{ __('Proyecto') }}</th>
            @foreach($quarterly_monthlist as $month)
                <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($quarterly_monthlist as $month)
                <th scope="col" class="br-1px">Budget</th>
                <th scope="col" class="br-1px">Actual</th>
                <th scope="col" class="br-1px">Over Budget</th>
            @endforeach
        </tr>
        </thead>

        <!----INCOME Category ---------------------->
        <tr>
            <th colspan="{{ count($quarterly_monthlist) * 3 + 4 }}" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
        </tr>

        @php $overBudgetTotal = []; @endphp

        @foreach ($incomeproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
                <td class="text-dark">{{ optional($productService->objetoGasto)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->programa)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->proyecto)->code ?? '-' }}</td>
                @foreach($quarterly_monthlist as $month)
                    @php
                        $budgetAmount = data_get($budget['income_data'], $productService->id.'.'.$month, 0);
                        $actualAmount = data_get($incomeArr, $productService->id.'.'.$month, 0);
                        $overBudgetAmount = $actualAmount - $budgetAmount;
                        $overBudgetTotal[$productService->id][$month] = $overBudgetAmount;

                        $pctBA = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                        $pctOB = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $overBudgetAmount) : 0;
                    @endphp

                    <td class="income_data {{$month}}_income">{{ \Auth::user()->priceFormat($budgetAmount) }}</td>
                    <td>
                        {{ \Auth::user()->priceFormat($actualAmount) }}
                        <p>{{ $pctBA ? '('.$pctBA.'%)' : '' }}</p>
                    </td>
                    <td>
                        {{ \Auth::user()->priceFormat($overBudgetAmount) }}
                        <p class="{{ $budgetAmount < $overBudgetAmount ? 'green-text' : '' }} {{ $budgetAmount > $overBudgetAmount ? 'red-text' : '' }}">{{ $pctOB ? '('.$pctOB.'%)' : '' }}</p>
                    </td>
                @endforeach
            </tr>
        @endforeach

        @php
            $overBudgetTotalArr = [];
            foreach($overBudgetTotal as $overBudget){
                foreach($overBudget as $k => $value){
                    $overBudgetTotalArr[$k] = ($overBudgetTotalArr[$k] ?? 0) + $value;
                }
            }
        @endphp

        <tr class="total">
            <td class="text-dark" colspan="4"><strong>{{__('Total :')}}</strong></td>
            @foreach($quarterly_monthlist as $month)
                @php
                    $bt = $budgetTotal[$month] ?? 0;
                    $it = $incomeTotalArr[$month] ?? 0;
                    $ob = $overBudgetTotalArr[$month] ?? 0;
                    $pctTI  = $bt ? \App\Models\Budget::percentage($bt, $it) : 0;
                    $pctTOB = $bt ? \App\Models\Budget::percentage($bt, $ob) : 0;
                @endphp
                <td class="text-dark {{$month}}_total_income"><strong>{{ \Auth::user()->priceFormat($bt) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($it) }}</strong>
                    <p>{{ $pctTI ? '('.$pctTI.'%)' : '' }}</p>
                </td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($ob) }}</strong>
                    <p class="{{ $bt < $ob ? 'green-text' : '' }} {{ $bt > $ob ? 'red-text' : '' }}">{{ $pctTOB ? '('.$pctTOB.'%)' : '' }}</p>
                </td>
            @endforeach
        </tr>

        <!------------ EXPENSE Category ---------------------->
        <tr>
            <th colspan="{{ count($quarterly_monthlist) * 3 + 4 }}" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
        </tr>

        @php $overExpenseBudgetTotal = []; @endphp

        @foreach ($expenseproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
                <td class="text-dark">{{ optional($productService->objetoGasto)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->programa)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->proyecto)->code ?? '-' }}</td>
                @foreach($quarterly_monthlist as $month)
                    @php
                        $budgetAmount = data_get($budget['expense_data'], $productService->id.'.'.$month, 0);
                        $actualAmount = data_get($expenseArr, $productService->id.'.'.$month, 0);
                        $overBudgetAmount = $actualAmount - $budgetAmount;
                        $overExpenseBudgetTotal[$productService->id][$month] = $overBudgetAmount;

                        $pctBA = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                        $pctOB = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $overBudgetAmount) : 0;
                    @endphp

                    <td class="expense_data {{$month}}_expense">{{ \Auth::user()->priceFormat($budgetAmount) }}</td>
                    <td>
                        {{ \Auth::user()->priceFormat($actualAmount) }}
                        <p>{{ $pctBA ? '('.$pctBA.'%)' : '' }}</p>
                    </td>
                    <td>
                        {{ \Auth::user()->priceFormat($overBudgetAmount) }}
                        <p class="{{ $budgetAmount < $overBudgetAmount ? 'green-text' : '' }} {{ $budgetAmount > $overBudgetAmount ? 'red-text' : '' }}">{{ $pctOB ? '('.$pctOB.'%)' : '' }}</p>
                    </td>
                @endforeach
            </tr>
        @endforeach

        @php
            $overExpenseBudgetTotalArr = [];
            foreach($overExpenseBudgetTotal as $overExpenseBudget){
                foreach($overExpenseBudget as $k => $value){
                    $overExpenseBudgetTotalArr[$k] = ($overExpenseBudgetTotalArr[$k] ?? 0) + $value;
                }
            }
        @endphp

        <tr class="total">
            <td class="text-dark" colspan="4"><strong>{{__('Total :')}}</strong></td>
            @foreach($quarterly_monthlist as $month)
                @php
                    $be = $budgetExpenseTotal[$month] ?? 0;
                    $et = $expenseTotalArr[$month] ?? 0;
                    $oeb = $overExpenseBudgetTotalArr[$month] ?? 0;
                    $pctTE   = $be ? \App\Models\Budget::percentage($be, $et) : 0;
                    $pctTOEB = $be ? \App\Models\Budget::percentage($be, $oeb) : 0;
                @endphp
                <td class="text-dark {{$month}}_total_expense"><strong>{{ \Auth::user()->priceFormat($be) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($et) }}</strong>
                    <p>{{ $pctTE ? '('.$pctTE.'%)' : '' }}</p>
                </td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($oeb) }}</strong>
                    <p class="{{ $be < $oeb ? 'green-text' : '' }} {{ $be > $oeb ? 'red-text' : '' }}">{{ $pctTOEB ? '('.$pctTOEB.'%)' : '' }}</p>
                </td>
            @endforeach
        </tr>

        <tfoot>
        <tr class="total" style="background:#f8f9fd;">
            <td class="text-dark" colspan="4"><strong>{{__('NET PROFIT :')}}</strong></td>
            @php
                $overbudgetprofit = [];
                foreach(array_unique(array_merge(array_keys($overBudgetTotalArr ?? []), array_keys($overExpenseBudgetTotalArr ?? []))) as $v){
                    $ob = $overBudgetTotalArr[$v] ?? 0;
                    $oe = $overExpenseBudgetTotalArr[$v] ?? 0;
                    $overbudgetprofit[$v] = $ob - $oe;
                }
            @endphp
            @foreach($quarterly_monthlist as $month)
                @php
                    $bp = $budgetprofit[$month] ?? 0;
                    $ap = $actualprofit[$month] ?? 0;
                    $obp = $overbudgetprofit[$month] ?? 0;
                @endphp
                <td class="text-dark"><strong>{{ \Auth::user()->priceFormat($bp) }}</strong></td>
                <td class="text-dark"><strong>{{ \Auth::user()->priceFormat($ap) }}</strong></td>
                <td class="text-dark"><strong>{{ \Auth::user()->priceFormat($obp) }}</strong>
                    <p class="{{ $bp < $obp ? 'green-text' : '' }} {{ $bp > $obp ? 'red-text' : '' }}"></p>
                </td>
            @endforeach
        </tr>
        </tfoot>
    </table>

{{--  Half -Yearly Budget--}}
@elseif($budget->period == 'half-yearly')
    <table class="table table-bordered table-item data">
        <thead>
        <tr>
            <th rowspan="2">{{ __('Category') }}</th>
            <th rowspan="2">{{ __('Objeto del gasto') }}</th>
            <th rowspan="2">{{ __('Programa') }}</th>
            <th rowspan="2">{{ __('Proyecto') }}</th>
            @foreach($half_yearly_monthlist as $month)
                <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($half_yearly_monthlist as $month)
                <th scope="col" class="br-1px">Budget</th>
                <th scope="col" class="br-1px">Actual</th>
                <th scope="col" class="br-1px">Over Budget</th>
            @endforeach
        </tr>
        </thead>

        <!----INCOME Category ---------------------->
        <tr>
            <th colspan="{{ count($half_yearly_monthlist) * 3 + 4 }}" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
        </tr>

        @php $overBudgetTotal = []; @endphp

        @foreach ($incomeproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
                <td class="text-dark">{{ optional($productService->objetoGasto)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->programa)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->proyecto)->code ?? '-' }}</td>
                @foreach($half_yearly_monthlist as $month)
                    @php
                        $budgetAmount = data_get($budget['income_data'], $productService->id.'.'.$month, 0);
                        $actualAmount = data_get($incomeArr, $productService->id.'.'.$month, 0);
                        $overBudgetAmount = $actualAmount - $budgetAmount;
                        $overBudgetTotal[$productService->id][$month] = $overBudgetAmount;

                        $pctBA = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                        $pctOB = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $overBudgetAmount) : 0;
                    @endphp

                    <td class="income_data {{$month}}_income">{{ \Auth::user()->priceFormat($budgetAmount) }}</td>
                    <td>
                        {{ \Auth::user()->priceFormat($actualAmount) }}
                        <p>{{ $pctBA ? '('.$pctBA.'%)' : '' }}</p>
                    </td>
                    <td>
                        {{ \Auth::user()->priceFormat($overBudgetAmount) }}
                        <p class="{{ $budgetAmount < $overBudgetAmount ? 'green-text' : '' }} {{ $budgetAmount > $overBudgetAmount ? 'red-text' : '' }}">{{ $pctOB ? '('.$pctOB.'%)' : '' }}</p>
                    </td>
                @endforeach
            </tr>
        @endforeach

        @php
            $overBudgetTotalArr = [];
            foreach($overBudgetTotal as $overBudget){
                foreach($overBudget as $k => $value){
                    $overBudgetTotalArr[$k] = ($overBudgetTotalArr[$k] ?? 0) + $value;
                }
            }
        @endphp

        <tr class="total">
            <td class="text-dark" colspan="4"><strong>{{__('Total :')}}</strong></td>
            @foreach($half_yearly_monthlist as $month)
                @php
                    $bt = $budgetTotal[$month] ?? 0;
                    $it = $incomeTotalArr[$month] ?? 0;
                    $ob = $overBudgetTotalArr[$month] ?? 0;
                    $pctTI  = $bt ? \App\Models\Budget::percentage($bt, $it) : 0;
                    $pctTOB = $bt ? \App\Models\Budget::percentage($bt, $ob) : 0;
                @endphp
                <td class="text-dark {{$month}}_total_income"><strong>{{ \Auth::user()->priceFormat($bt) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($it) }}</strong>
                    <p>{{ $pctTI ? '('.$pctTI.'%)' : '' }}</p>
                </td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($ob) }}</strong>
                    <p class="{{ $bt < $ob ? 'green-text' : '' }} {{ $bt > $ob ? 'red-text' : '' }}">{{ $pctTOB ? '('.$pctTOB.'%)' : '' }}</p>
                </td>
            @endforeach
        </tr>

        <!------------ EXPENSE Category ---------------------->
        <tr>
            <th colspan="{{ count($half_yearly_monthlist) * 3 + 4 }}" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
        </tr>

        @php $overExpenseBudgetTotal = []; @endphp

        @foreach ($expenseproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
                <td class="text-dark">{{ optional($productService->objetoGasto)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->programa)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->proyecto)->code ?? '-' }}</td>
                @foreach($half_yearly_monthlist as $month)
                    @php
                        $budgetAmount = data_get($budget['expense_data'], $productService->id.'.'.$month, 0);
                        $actualAmount = data_get($expenseArr, $productService->id.'.'.$month, 0);
                        $overBudgetAmount = $actualAmount - $budgetAmount;
                        $overExpenseBudgetTotal[$productService->id][$month] = $overBudgetAmount;

                        $pctBA = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                        $pctOB = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $overBudgetAmount) : 0;
                    @endphp

                    <td class="expense_data {{$month}}_expense">{{ \Auth::user()->priceFormat($budgetAmount) }}</td>
                    <td>
                        {{ \Auth::user()->priceFormat($actualAmount) }}
                        <p>{{ $pctBA ? '('.$pctBA.'%)' : '' }}</p>
                    </td>
                    <td>
                        {{ \Auth::user()->priceFormat($overBudgetAmount) }}
                        <p class="{{ $budgetAmount < $overBudgetAmount ? 'green-text' : '' }} {{ $budgetAmount > $overBudgetAmount ? 'red-text' : '' }}">{{ $pctOB ? '('.$pctOB.'%)' : '' }}</p>
                    </td>
                @endforeach
            </tr>
        @endforeach

        @php
            $overExpenseBudgetTotalArr = [];
            foreach($overExpenseBudgetTotal as $overExpenseBudget){
                foreach($overExpenseBudget as $k => $value){
                    $overExpenseBudgetTotalArr[$k] = ($overExpenseBudgetTotalArr[$k] ?? 0) + $value;
                }
            }
        @endphp

        <tr class="total">
            <td class="text-dark" colspan="4"><strong>{{__('Total :')}}</strong></td>
            @foreach($half_yearly_monthlist as $month)
                @php
                    $be = $budgetExpenseTotal[$month] ?? 0;
                    $et = $expenseTotalArr[$month] ?? 0;
                    $oeb = $overExpenseBudgetTotalArr[$month] ?? 0;
                    $pctTE   = $be ? \App\Models\Budget::percentage($be, $et) : 0;
                    $pctTOEB = $be ? \App.Models\Budget::percentage($be, $oeb) : 0;
                @endphp
                <td class="text-dark {{$month}}_total_expense"><strong>{{ \Auth::user()->priceFormat($be) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($et) }}</strong>
                    <p>{{ $pctTE ? '('.$pctTE.'%)' : '' }}</p>
                </td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($oeb) }}</strong>
                    <p class="{{ $be < $oeb ? 'green-text' : '' }} {{ $be > $oeb ? 'red-text' : '' }}">{{ $pctTOEB ? '('.$pctTOEB.'%)' : '' }}</p>
                </td>
            @endforeach
        </tr>

        <tfoot>
        <tr class="total" style="background:#f8f9fd;">
            <td class="text-dark" colspan="4"><strong>{{__('NET PROFIT :')}}</strong></td>
            @php
                $overbudgetprofit = [];
                foreach(array_unique(array_merge(array_keys($overBudgetTotalArr ?? []), array_keys($overExpenseBudgetTotalArr ?? []))) as $v){
                    $ob = $overBudgetTotalArr[$v] ?? 0;
                    $oe = $overExpenseBudgetTotalArr[$v] ?? 0;
                    $overbudgetprofit[$v] = $ob - $oe;
                }
            @endphp
            @foreach($half_yearly_monthlist as $month)
                @php
                    $bp = $budgetprofit[$month] ?? 0;
                    $ap = $actualprofit[$month] ?? 0;
                    $obp = $overbudgetprofit[$month] ?? 0;
                @endphp
                <td class="text-dark"><strong>{{ \Auth::user()->priceFormat($bp) }}</strong></td>
                <td class="text-dark"><strong>{{ \Auth::user()->priceFormat($ap) }}</strong></td>
                <td class="text-dark"><strong>{{ \Auth::user()->priceFormat($obp) }}</strong>
                    <p class="{{ $bp < $obp ? 'green-text' : '' }} {{ $bp > $obp ? 'red-text' : '' }}"></p>
                </td>
            @endforeach
        </tr>
        </tfoot>
    </table>

{{-- Yearly Budget--}}
@else
    <table class="table table-bordered table-item data">
        <thead>
        <tr>
            <th rowspan="2">{{ __('Category') }}</th>
            <th rowspan="2">{{ __('Objeto del gasto') }}</th>
            <th rowspan="2">{{ __('Programa') }}</th>
            <th rowspan="2">{{ __('Proyecto') }}</th>
            @foreach($yearly_monthlist as $month)
                <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($yearly_monthlist as $month)
                <th scope="col" class="br-1px">Budget</th>
                <th scope="col" class="br-1px">Actual</th>
                <th scope="col" class="br-1px">Over Budget</th>
            @endforeach
        </tr>
        </thead>

        <!----INCOME Category ---------------------->
        <tr>
            <th colspan="{{ count($yearly_monthlist) * 3 + 4 }}" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
        </tr>

        @php $overBudgetTotal = []; @endphp

        @foreach ($incomeproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
                <td class="text-dark">{{ optional($productService->objetoGasto)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->programa)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->proyecto)->code ?? '-' }}</td>
                @foreach($yearly_monthlist as $month)
                    @php
                        $budgetAmount = data_get($budget['income_data'], $productService->id.'.'.$month, 0);
                        $actualAmount = data_get($incomeArr, $productService->id.'.'.$month, 0);
                        $overBudgetAmount = $actualAmount - $budgetAmount;
                        $overBudgetTotal[$productService->id][$month] = $overBudgetAmount;

                        $pctBA = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                        $pctOB = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $overBudgetAmount) : 0;
                    @endphp

                    <td class="income_data {{$month}}_income">{{ \Auth::user()->priceFormat($budgetAmount) }}</td>
                    <td>
                        {{ \Auth::user()->priceFormat($actualAmount) }}
                        <p>{{ $pctBA ? '('.$pctBA.'%)' : '' }}</p>
                    </td>
                    <td>
                        {{ \Auth::user()->priceFormat($overBudgetAmount) }}
                        <p class="{{ $budgetAmount < $overBudgetAmount ? 'green-text' : '' }} {{ $budgetAmount > $overBudgetAmount ? 'red-text' : '' }}">{{ $pctOB ? '('.$pctOB.'%)' : '' }}</p>
                    </td>
                @endforeach
            </tr>
        @endforeach

        @php
            $overBudgetTotalArr = [];
            foreach($overBudgetTotal as $overBudget){
                foreach($overBudget as $k => $value){
                    $overBudgetTotalArr[$k] = ($overBudgetTotalArr[$k] ?? 0) + $value;
                }
            }
        @endphp

        <tr class="total text-dark">
            <td colspan="4"><strong>{{__('Total :')}}</strong></td>
            @foreach($yearly_monthlist as $month)
                @php
                    $bt = $budgetTotal[$month] ?? 0;
                    $it = $incomeTotalArr[$month] ?? 0;
                    $ob = $overBudgetTotalArr[$month] ?? 0;
                    $pctTI  = $bt ? \App\Models\Budget::percentage($bt, $it) : 0;
                    $pctTOB = $bt ? \App\Models\Budget::percentage($bt, $ob) : 0;
                @endphp
                <td class="text-dark {{$month}}_total_income"><strong>{{ \Auth::user()->priceFormat($bt) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($it) }}</strong>
                    <p>{{ $pctTI ? '('.$pctTI.'%)' : '' }}</p>
                </td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($ob) }}</strong>
                    <p class="{{ $bt < $ob ? 'green-text' : '' }} {{ $bt > $ob ? 'red-text' : '' }}">{{ $pctTOB ? '('.$pctTOB.'%)' : '' }}</p>
                </td>
            @endforeach
        </tr>

        <!------------ EXPENSE Category ---------------------->
        <tr>
            <th colspan="{{ count($yearly_monthlist) * 3 + 4 }}" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
        </tr>

        @php $overExpenseBudgetTotal = []; @endphp

        @foreach ($expenseproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
                <td class="text-dark">{{ optional($productService->objetoGasto)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->programa)->code ?? '-' }}</td>
                <td class="text-dark">{{ optional($productService->proyecto)->code ?? '-' }}</td>
                @foreach($yearly_monthlist as $month)
                    @php
                        $budgetAmount = data_get($budget['expense_data'], $productService->id.'.'.$month, 0);
                        $actualAmount = data_get($expenseArr, $productService->id.'.'.$month, 0);
                        $overBudgetAmount = $actualAmount - $budgetAmount;
                        $overExpenseBudgetTotal[$productService->id][$month] = $overBudgetAmount;

                        $pctBA = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $actualAmount) : 0;
                        $pctOB = $budgetAmount ? \App\Models\Budget::percentage($budgetAmount, $overBudgetAmount) : 0;
                    @endphp
                    <td class="expense_data {{$month}}_expense">{{ \Auth::user()->priceFormat($budgetAmount) }}</td>
                    <td>
                        {{ \Auth::user()->priceFormat($actualAmount) }}
                        <p>{{ $pctBA ? '('.$pctBA.'%)' : '' }}</p>
                    </td>
                    <td>
                        {{ \Auth::user()->priceFormat($overBudgetAmount) }}
                        <p class="{{ $budgetAmount < $overBudgetAmount ? 'green-text' : '' }} {{ $budgetAmount > $overBudgetAmount ? 'red-text' : '' }}">{{ $pctOB ? '('.$pctOB.'%)' : '' }}</p>
                    </td>
                @endforeach
            </tr>
        @endforeach

        @php
            $overExpenseBudgetTotalArr = [];
            foreach($overExpenseBudgetTotal as $overExpenseBudget){
                foreach($overExpenseBudget as $k => $value){
                    $overExpenseBudgetTotalArr[$k] = ($overExpenseBudgetTotalArr[$k] ?? 0) + $value;
                }
            }
        @endphp

        <tr class="total">
            <td class="text-dark" colspan="4"><strong>{{__('Total :')}}</strong></td>
            @foreach($yearly_monthlist as $month)
                @php
                    $be = $budgetExpenseTotal[$month] ?? 0;
                    $et = $expenseTotalArr[$month] ?? 0;
                    $oeb = $overExpenseBudgetTotalArr[$month] ?? 0;
                    $pctTE   = $be ? \App\Models\Budget::percentage($be, $et) : 0;
                    $pctTOEB = $be ? \App\Models\Budget::percentage($be, $oeb) : 0;
                @endphp
                <td class="text-dark {{$month}}_total_expense"><strong>{{ \Auth::user()->priceFormat($be) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($et) }}</strong>
                    <p>{{ $pctTE ? '('.$pctTE.'%)' : '' }}</p>
                </td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($oeb) }}</strong>
                    <p class="{{ $be < $oeb ? 'green-text' : '' }} {{ $be > $oeb ? 'red-text' : '' }}">{{ $pctTOEB ? '('.$pctTOEB.'%)' : '' }}</p>
                </td>
            @endforeach
        </tr>

        <tfoot>
        <tr class="total" style="background:#f8f9fd;">
            <td class="text-dark" colspan="4"><strong>{{__('NET PROFIT :')}}</strong></td>
            @php
                $overbudgetprofit = [];
                foreach(array_unique(array_merge(array_keys($overBudgetTotalArr ?? []), array_keys($overExpenseBudgetTotalArr ?? []))) as $v){
                    $ob = $overBudgetTotalArr[$v] ?? 0;
                    $oe = $overExpenseBudgetTotalArr[$v] ?? 0;
                    $overbudgetprofit[$v] = $ob - $oe;
                }
            @endphp
            @foreach($yearly_monthlist as $month)
                @php
                    $bp = $budgetprofit[$month] ?? 0;
                    $ap = $actualprofit[$month] ?? 0;
                    $obp = $overbudgetprofit[$month] ?? 0;
                @endphp
                <td class="text-dark"><strong>{{ \Auth::user()->priceFormat($bp) }}</strong></td>
                <td class="text-dark">
                    <strong>{{ \Auth::user()->priceFormat($ap) }}</strong>
                    <p>{{ $bp ? '('.(\App\Models\Budget::percentage($bp, $ap)).'%)' : '' }}</p>
                </td>
                <td class="text-dark"><strong>{{ \Auth::user()->priceFormat($obp) }}</strong>
                    <p class="{{ $bp < $obp ? 'green-text' : '' }} {{ $bp > $obp ? 'red-text' : '' }}"></p>
                </td>
            @endforeach
        </tr>
        </tfoot>
    </table>
@endif

</div>
</div>
</div>
</div>
@endsection
