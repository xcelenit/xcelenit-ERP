@extends('layouts.app')
@section('title', 'Acconting')
 
@section('content')
@include('accounting::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Transactions</h1>
</section>



<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        @include('accounting::transactions.partials.sell_list_filters')
    @endcomponent

    @component('components.widget', ['class' => 'box-solid'])
        
        <div class="table-responsive">
            <table class="table table-bordered" id="transaction_table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">Action</th>
                        <th width="10%"  class="text-center">Date</th>
                        <th width="10%"  class="text-center">Doc No</th>                         
                        <th width="40%" class="text-center">Description</th> 
                        <th width="10%" class="text-center">Total Amount</th>
                        <th width="10%"  class="text-center">UnAdjusted</th>    
                        <th width="10%"  class="text-center">Total Paid</th>                    
                        <th width="10%"  class="text-center">Cheque No</th>
                        <th width="5%"  class="text-center">PMT.Status</th> 
                        <th width="5%"  class="text-center">Status</th>                        
                    </tr>
                </thead>   
                <tfoot>
                    <tr class="bg-gray font-17 footer-total text-center">
                        <td colspan="4" >Total :</td>
                        <td > <span class="display_currency" id="footer_total" data-currency_symbol ="true"></span></td>   
                        <td colspan="5"></td>
                    </tr>
                </tfoot>             
            </table>
        </div>
    @endcomponent
</section>
<!-- /.content -->


@stop
@section('javascript')
<script type="text/javascript">
	$(document).ready( function () {

        $('#sell_list_filter_date_range').daterangepicker( 
            dateRangeSettings,
            function (start, end) {
                $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                transaction_table.ajax.reload();
            }
        );
        
        $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#sell_list_filter_date_range').val('');
            transaction_table.ajax.reload();
        });


     

		transaction_table = $('#transaction_table').DataTable({
	        processing: true,
	        serverSide: true,
	        ajax:{
                url: '{{action("\Modules\Accounting\Http\Controllers\DoubleEntryAccountTransactionController@getData")}}',
                method: 'POST',
                data: function(d) {
                    var start = '';
                    var end = '';
                    if ($('#sell_list_filter_date_range').val()) {
                                start = $('input#sell_list_filter_date_range')
                                    .data('daterangepicker')
                                    .startDate.format('YYYY-MM-DD');
                                end = $('input#sell_list_filter_date_range')
                                    .data('daterangepicker')
                                    .endDate.format('YYYY-MM-DD');
                    }

                    d.start_date = start;
                    d.end_date = end;
                    d.vendor_id = $('select#vendor_id').val();   
                    d.document_type = $('select#document_type').val();  
                    d.user_id = $('select#added_by').val();
                    d.payment_status = $('select#payment_status').val();    
                    d.location_id = $('select#location_id').val();  
                    d.ref_no = $('#ref_no').val();  

                },
            } ,
             
	        "order": [[ 1, "desc" ]],
            "columnDefs": [
                { className: "text-right", "targets": [4,5,6] },
                { className: "text-center", "targets": [1,7,8,9] },
                ] ,  
            "createdRow": function( row, data, dataIndex){
               // console.log(data.is_canceled)
                if( data.is_canceled ==  1){
                    $(row).addClass('bg-danger');
                }
            }, 
	        columns: [	 
                { data: 'action', name: 'action', orderable:false },  
                { data: 'transaction_date', name: 'transaction_date' },
	            { data: 'document_no', name: 'document_no' },
				// { data: 'document_type', name: 'document_type' },
	            // { data: 'vendor_name', name: 'vendor_name' },
	            { data: 'payment_note', name: 'payment_note',  orderable:false},	
	            { data: 'total_amount', name: 'total_amount'},	
	            { data: 'total_unaj_amount', name: 'total_unaj_amount'},
	            { data: 'total_paid', name: 'total_paid', searchable: false},
	            { data: 'cheque_no', name: 'cheque_no', visible:false},	
	            { data: 'payment_status_info', name: 'payment_status_info', searchable: false, visible:false, orderable:false},
	            { data: 'status', name: 'status', orderable:false },                        
	            
	        ],
	        fnDrawCallback: function(oSettings) {
	            

                $('#footer_total').text(
                            sum_table_col($('#transaction_table'), 'row_total')
                );

                __currency_convert_recursively($('#transaction_table'));
	        },
		});
		
		$(document).on('change', '#added_by', function() {
            transaction_table.ajax.reload();
		});

        $(document).on('change', '#location_id', function() {
            transaction_table.ajax.reload();
		});

       

        $(document).on('click', '.cheque-print', function() {
            transaction_table.ajax.reload();
		});

         

        $(document).on('change', '#payment_status', function() {
            transaction_table.ajax.reload();
		});

        $(document).on('change', '#ref_no', function() {
            transaction_table.ajax.reload();
		});

        $(document).on('change', '#vendor_id', function() {
            transaction_table.ajax.reload();
		});

        $(document).on('change', '#document_type', function() {
            transaction_table.ajax.reload();
		});
        				
	});

</script>
@endsection
