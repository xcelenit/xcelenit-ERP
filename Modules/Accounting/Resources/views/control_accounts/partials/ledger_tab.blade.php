@php
    $transaction_types = [];
    if($control_ac_type=='creditors'){
        $transaction_types['purchase'] = __('lang_v1.purchase');
        $transaction_types['purchase_return'] = __('lang_v1.purchase_return');
    }

    if($control_ac_type=='debitors'){
        $transaction_types['sell'] = __('sale.sale');
        $transaction_types['sell_return'] = __('lang_v1.sell_return');
    }

    $transaction_types['opening_balance'] = __('lang_v1.opening_balance');
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('ledger_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('ledger_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>       
    </div>
    <div class="col-md-6 col-sm-6 col-xs-6 text-right align-right">
        <h3 class="mb-0 blue-heading p-4">@lang('lang_v1.account_summary')</h3>        
        <table class="table table-condensed text-left align-left no-border">
            <tr>
                <td>@lang('lang_v1.opening_balance')</td>
                <td id="opening_bl_id" class="align-right">0.00</td>
            </tr>
        <tr>
            <td>Total Debit</td>
            <td id="total_debit_id" class="align-right">0.00</td>
        </tr>
        <tr>
            <td>Total Credit</td>
            <td id="total_credit_id" class="align-right">0.00</td>
        </tr>
        <tr>
            <td><strong>Closing Balance</strong></td>
            <td id="closing_balance_id" class="align-right">0.00</td>
        </tr>
        </table>
    </div>
<div class="col-md-12 col-sm-12">
    <hr>
{{-- <p class="text-center" style="text-align: center;"><strong id=""></strong></p> --}}
<div class="table-responsive">
    <table class="table table-striped table-sm table-bordered" id="ledger_table">
        <thead>
            <tr class="row-border blue-heading">
                <th width="12%">Date</th>
                <th width="10%">Reference</th>
                <th width="38%">Description</th>
                <th class="text-center" width="10%">Debit</th>
                <th class="text-center" width="10%">Credit</th>
                <th class="text-center" width="20%">Balance</th>            
            </tr>
        </thead>
        <tbody>       
        </tbody>
    </table>
</div>
</div>
</div>