@extends('layouts.app')
@section('title', 'Account Ledger')

@section('content')
@include('accounting::layouts.nav')

<!-- Main content -->
<section class="content no-print">
    <div class="row no-print">
        <div class="col-md-4">
            <h3>Account Ledger</h3>
        </div>
        <div class="col-md-4 col-xs-12 mt-15 pull-right">
            {!! Form::select('account_id', $accounts, (isset($set_account_id) ? $set_account_id : null)  , ['class' => 'form-control select2', 'id' => 'account_id']); !!}
        </div>
    </div>
    <br>    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified">
                    <li class="active">
                        <a href="#ledger_tab" data-toggle="tab" aria-expanded="true"><i class="fas fa-scroll" aria-hidden="true"></i> @lang('lang_v1.ledger')</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="ledger_tab">
                        @include('accounting::accounts.partials.ledger_tab')
                    </div>
                </div>
            </div>
           
        </div>
    </div>
</section>

@stop

@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
    
    $('#ledger_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#ledger_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
        }
    );

    $('#ledger_date_range').change( function(){
        account_ledger_table.ajax.reload();  
      //  get_accountSummary();      
    });
 
    $('#account_id').change( function() {
        account_ledger_table.ajax.reload();
       // get_accountSummary();
    });


    account_ledger_table = $('#ledger_table').DataTable({
	        processing: true,
	        serverSide: true,
            ajax: {
                url: '/accounting/ledger/data',
                method: 'POST',
                data: function(d) {
                    var start = '';
                    var end = '';
                    if ($('#ledger_date_range').val()) {
                                start = $('input#ledger_date_range')
                                    .data('daterangepicker')
                                    .startDate.format('YYYY-MM-DD');
                                end = $('input#ledger_date_range')
                                    .data('daterangepicker')
                                    .endDate.format('YYYY-MM-DD');
                    }

                    d.start_date = start;
                    d.end_date = end;
                    d.account_id = $('select#account_id').val();
                                      
                },
            },  
            "columnDefs": [
                { className: "text-right", "targets": [3,4,5] },
                ] ,                
	        //"order": [[ 5, "desc" ]],
	        columns: [	 
                { data: 'transaction_date', name: 'transaction_date', searchable:false, orderable:false },    
                { data: 'document_no', name: 'AT.document_no' ,orderable:false},
	            { data: 'desc', name: 'desc' ,orderable:false},
				{ data: 'debit', name: 'debit' ,orderable:false,searchable:false},
	            { data: 'credit', name: 'credit' ,orderable:false,searchable:false },
	            { data: 'balance', name: 'balance',orderable:false,searchable:false},		            
	        ],
	        fnDrawCallback: function(oSettings) {
                get_accountSummary() ;
                //  console.log(oSettings)
	            // __currency_convert_recursively($('#ledger_table'));
	        },
		});


});

function get_accountSummary() 
{   
                     
    var account_id = $('select#account_id').val();
     

    $.ajax({
        url: '/accounting/ledger/data/summary',
        dataType: 'JSON',
        method: 'POST',
        data:{account_id: account_id},        
        success: function(result) {           
            $('#opening_bl_id').html(result.opening_balance); 
            $('#total_debit_id').html(result.total_debit); 
            $('#total_credit_id').html(result.total_credit); 
            $('#closing_balance_id').html(result.account_balance);                    
        },
    });
}

</script>
<style>

#ledger_table tbody td {
    padding-top: 1px;
    padding-bottom: 1px;
}
#ledger_table tbody th {
    padding-top: 3px;
    padding-bottom: 3px;
}

</style>

@endsection