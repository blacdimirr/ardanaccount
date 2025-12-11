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
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-body table-border-style custom_temp">

{{--  Monthly Budget--}}
@if($budget->period == 'monthly')
    <table class="table table-bordered table-item data">
        <thead>
        <tr>
            <td rowspan="2"></td>
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
            <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
        </tr>

        @php $overBudgetTotal = []; @endphp

        @foreach ($incomeproduct as $productService)
            <tr>
                <td class="text-dark">{{ $productService->name }}</td>
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
            <td class="text-dark"><strong>{{__('Total :')}}</strong></td>
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
            <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
        </tr>

        @php $overExpenseBudgetTotal = []; @endphp

        @foreach ($expenseproduct as $productService)
            <tr>
                <td class="text-dark">{{ $productService->name }}</td>
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
            <td class="text-dark"><strong>{{__('Total :')}}</strong></td>
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
            <td class="text-dark"><strong>{{__('NET PROFIT :')}}</strong></td>
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
            <td rowspan="2"></td>
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
            <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
        </tr>

        @php $overBudgetTotal = []; @endphp

        @foreach ($incomeproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
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
            <td class="text-dark"><strong>{{__('Total :')}}</strong></td>
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
            <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
        </tr>

        @php $overExpenseBudgetTotal = []; @endphp

        @foreach ($expenseproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
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
            <td class="text-dark"><strong>{{__('Total :')}}</strong></td>
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
            <td class="text-dark"><strong>{{__('NET PROFIT :')}}</strong></td>
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
            <td rowspan="2"></td>
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
            <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
        </tr>

        @php $overBudgetTotal = []; @endphp

        @foreach ($incomeproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
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
            <td class="text-dark"><strong>{{__('Total :')}}</strong></td>
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
            <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
        </tr>

        @php $overExpenseBudgetTotal = []; @endphp

        @foreach ($expenseproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
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
            <td class="text-dark"><strong>{{__('Total :')}}</strong></td>
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
            <td class="text-dark"><strong>{{__('NET PROFIT :')}}</strong></td>
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
            <td rowspan="2"></td>
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
            <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
        </tr>

        @php $overBudgetTotal = []; @endphp

        @foreach ($incomeproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
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
            <td><strong>{{__('Total :')}}</strong></td>
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
            <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
        </tr>

        @php $overExpenseBudgetTotal = []; @endphp

        @foreach ($expenseproduct as $productService)
            <tr>
                <td class="text-dark">{{$productService->name}}</td>
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
            <td class="text-dark"><strong>{{__('Total :')}}</strong></td>
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
            <td class="text-dark"><strong>{{__('NET PROFIT :')}}</strong></td>
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
