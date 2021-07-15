@extends('layouts.app')
@section('title', 'Bank Deposit')

@section('content')
@include('accounting::layouts.nav')

<!-- Main content -->
<section class="content no-print">
    <div class="row no-print">
        <div class="col-sm-8">
            <h3>BANK DEPOSIT</h3>
        </div>
        <div class="col-md-4 col-xs-12 mt-15 pull-right">
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
                {{-- {!! Form::text('date_of_transctions', @format_date('now'), ['class' => 'form-control', 'id'=> 'date_of_transctions', 'readonly', 'required']); !!}                                 --}}
                {!! Form::text('filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'),'id'=>'filter_date_range', 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    </div>
    <br>   
    @php
        $total_cash_in_hand =0;
        $total_cheque_in_hand =0;
    @endphp
    @component('components.widget', ['class' => 'box-solid'])
    <div class="row">                                                    
        <div class="col-sm-12 col-md-12">                                    
            <div class="s">
                
                <table class="table table-bordered dp-table">
                    <thead>                       
                        <tr class="row-border blue-heading">
                            <th colspan="2" class="text-center" width="15%">ACCOUNT CODE</th>
                            <th class="text-center" width="45%">ACCOUNT</th>
                            <th colspan="2" class="text-center" width="30%">IN HAND BALANCE</th> 
                            <th class="text-center" width="10%">ACTION</th>       
                        </tr>
                        @foreach ($cheque_in_hand_accounts as $account)
                        <tr style="font-size: 16px;" class="acc-type">
                            <th colspan="2">{{$account['account_code']}}</th>
                            <th>{{$account['account_name']}} </th>
                            <th colspan="2" class="text-right">{{number_format($account['balance'],2,'.',',')}}</th>  
                            <th class="text-center">
                                <a class="btn btn-info btn-sm view_btn_{{$account['id']}}"  onclick="getTransactionData('{{$account['id']}}','{{$account['account_name']}}')">View</a>
                                {{-- <a class="btn btn-success btn-sm">Deposit</a> --}}
                            </th>                          
                        </tr>
                            @php
                                $total_cheque_in_hand +=$account['balance'];                             
                            @endphp    
                       
                        @endforeach
                        <tr  style="font-size: 16px;" class="bg-success">
                            <th class="text-right" colspan="3"> TOTAL AMOUNT :</th>
                            <th colspan="2" class="text-right">{{number_format($total_cheque_in_hand,2,'.',',')}}</th> 
                            <th></th> 
                        </tr> 
                        <tr class="bg-danger">
                            <th width="3%" class="text-center"> <input id="select_all_checkbox" type="checkbox"> </th>
                            <th class="text-center">CHEQUE NO</th>
                            <th class="text-center">PMT METHOD | DOC NO - CUSTOMER NAME - More</th>
                            <th class="text-center">RECEIPT DATE</th>
                            <th colspan="2" class="text-center">AMOUNT</th>
                             
                        </tr>
                    </thead>
                    <tbody id="in_hand_tbody">                
                         
                    </tbody>
                    <tfoot>                         
                        <tr  class="bg-warning">
                            <th class="text-right" colspan="4"> TOTAL SELECTED:</th>
                            <th colspan="2" class="text-right" id="total_selected_val"></th>                             
                        </tr>                      
                    </tfoot>
                </table>

                <table class="custom_table">
                    <tbody>
                        <tr>
                            <td class="col-sm-2 text-left">
                                <strong class="">DOCUMENT TYPE :</strong>
                            </td> 
                            <td class="col-sm-2">                            
                                {!! Form::text('doc_type', $document_type_code, ['placeholder' => '', 'class' => 'form-control', 'required', 'disabled']); !!}
                            </td>
                            <td class="col-sm-2 text-left">
                                <strong class="">TRN. DATE :</strong>
                            </td> 
                            <td class="col-sm-2">                            
                                {{-- {!! Form::text('doc_type', '', ['placeholder' => '', 'class' => 'form-control','required']); !!} --}}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    {!! Form::text('transaction_date', null, ['class' => 'form-control', 'id'=> 'transaction_date', 'readonly', 'required']); !!}                                
                                </div>
                                <span id="transaction_date_error" class="text-danger"></span>
                            </td> 
                            <td class="col-sm-2 text-left">
                                <strong class="">PERIOD :</strong>
                            </td> 
                            <td class="col-sm-2">                            
                                {!! Form::text('period', 2020, ['placeholder' => '', 'class' => 'form-control', 'required', 'disabled']); !!}
                            </td>
                        </tr> 
                        <tr>
                            <td class="col-sm-2 text-left bg-success">
                                <strong class="">CREDIT ACCOUNT :</strong>
                            </td>  
                            <td colspan="3" class="col-sm-6" > 
                                {!! Form::text('credit_account_name', null, ['placeholder' => '','id'=>'credit_account_name', 'class' => 'form-control text-left', 'disabled']); !!}
                                <input type="hidden" id="credit_account_id" value="">
                            </td>
                            <td class="col-sm-2 text-left">
                                <strong class="">AMOUNT :</strong>
                            </td>
                            <td  class="col-sm-2">
                                {!! Form::text('amount', '0.00', ['placeholder' => '','id'=>'amount', 'class' => 'form-control text-right', 'disabled']); !!}   
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-2 text-left bg-success">
                                <strong class="">DEBIT ACCOUNT :</strong>
                            </td>  
                            <td colspan="3" class="col-sm-6" >                           
                                {!! Form::select('debit_account_id', $credit_account_list, null, ['id' => 'debit_account_id' ,'class' => 'form-control select2', 'required']); !!}
                                <span id="dr_account_id_error" class="text-danger"></span>
                            </td>
                            <td colspan="2" class="col-sm-4 text-center">
                                <div class="checkbox">
                                    <label>
                                      <input type="checkbox" id="ad_as_single_entry" autocomplete="false" value="" class="" > &nbsp;&nbsp;&nbsp;ADD AS A SINGLE ENTRY
                                    </label>
                                </div>
                            </td> 
                             
                        </tr>
                        <tr>
                            <td class="col-sm-2 text-left">
                                <strong class="">PAYMENT NOTE :</strong> (Max 255)
                            </td> 
                            <td colspan="5" class="col-sm-10">                            
                                {!! Form::textarea('payment_note', '', ['placeholder' => '','rows' => 1, 'cols' => 54, 'id'=>'payment_note','class' => 'form-control','required']); !!}
                            </td>                                                
                        </tr>  
                        <tr>                           
                            <td colspan="6" style="padding: 5px;" class="col-sm-12"> 
                                <span id="rows_not_selected_error" class="text-danger"></span>
                                 <input type="hidden" value="0" id="has_emt_cheque_no">
                                <button type="button" id="btn_deposit" style="margin-left: 10px" onclick="storeData()" class="btn btn-primary pull-right">Save (DEPOSIT)</button>                                 
                            </td>                                                
                        </tr>                        
                    </tbody>
                </table> 

                
            </div>
        </div>
    </div>  
    @endcomponent   
</section>

@stop

@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
       
        // $('#date_of_transctions').datepicker({
        //     autoclose: true,
        //     format: datepicker_date_format
        // });

        // $('#date_of_transctions').change( function() {            
        //     var date = $('#date_of_transctions').val();             
        // });


        $('#transaction_date').datetimepicker({
            //autoclose: true,
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });

        // $('#transaction_date').change( function() {            
        //     var date = $('#transaction_date').val();             
        // });

        $('#filter_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
              //  transaction_table.ajax.reload();
              $("#in_hand_tbody").html(null);	
            }
        );
        
        $('#filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            // $('#filter_date_range').val('');
            // transaction_table.ajax.reload();
        });

        

        $('#select_all_checkbox').change(function() {
            
            var checked =null;
            var total_amount=0;

            if($('#select_all_checkbox').prop('checked')) {
                 checked =true;
            } else {
                 checked =false;
            }

            $('#in_hand_tbody tr').each(function() {
                $(this).find(".transaction_id").prop('checked',checked); 
                if(checked){
                    $(this).addClass("bg-info");
                    total_amount += parseFloat($(this).find(".amount").val());

                    if($(this).find('.payment_method').val()=='cheque'){
                        if(($(this).find('.cheque_no').val()==null) || ($(this).find('.cheque_no').val()=='')){
                            $(this).find('.cheque_no').addClass("bg-danger");                            
                        }else{
                            $(this).find('.cheque_no').removeClass("bg-danger");
                        }
                    }

                }else{
                    $(this).removeClass("bg-info");
                     // $(this).find('.cheque_no').removeClass("bg-danger");
                        if(isNaN($(this).find('.cheque_no').val())){
                           $('#rows_not_selected_error').html('* Cheque numbers should be a number. Please enter check numbers to continue.');
                          // $(this).find('.cheque_no').addClass("bg-danger");                                 
                        }else{
                           $(this).find('.cheque_no').removeClass("bg-danger");
                        }
                }
            });

            // console.log(total_amount);
            $('#total_selected_val').html(total_amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"));
            $('#amount').val(total_amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"));

        });

        // $('.transaction_id').change(function() {
        //     console.log('asda');

           
        // });

        resetFormData();
        
});

    function selectRow(){
        
        var total_amount = 0;
        var data_array = []
         $('#in_hand_tbody tr').each(function() { 
              
                if(($(this).find(".transaction_id").prop('checked'))){
                    $(this).addClass("bg-info");
                    total_amount += parseFloat($(this).find(".amount").val());

                    if($(this).find('.payment_method').val()=='cheque'){
                        if(($(this).find('.cheque_no').val()==null) || ($(this).find('.cheque_no').val()=='')){
                            $(this).find('.cheque_no').addClass("bg-danger");   
                        }else{
                            
                            if(isNaN($(this).find('.cheque_no').val())){
                                $('#rows_not_selected_error').html('* Cheque numbers should be a number. Please enter check numbers to continue.');
                                $(this).find('.cheque_no').addClass("bg-danger");                                 
                            }else{
                                $(this).find('.cheque_no').removeClass("bg-danger");
                            }
                        }
                    }

                    data_array.push({
                        perent_transaction_id: parseInt($(this).find(".transaction_id").val()), 
                        sub_amount: $(this).find(".amount").val(), 
                        ref_no: $(this).find(".document_no").val(), 
                        payment_method: $(this).find(".payment_method").val(),  
                        cheque_no: $(this).find(".cheque_no").val(),
                        cheque_date : $(this).find(".cheque_date").val(),
                        })

                }else{
                    $(this).removeClass("bg-info");
                    $(this).find('.cheque_no').removeClass("bg-danger");

                    
                    
                }
         });

        // console.log(total_amount.toFixed(2));
         $('#total_selected_val').html(total_amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"))
         $('#amount').val(total_amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"));

         return data_array;
    }

    function storeData(){
        
         var isValidated = validateData();
           if(isValidated==false){  
            toastr.error('Something went wrong');           
            return null
           }

           
            //   var vendor_id =   $('#vendor_id').val();
            //   var location_id =   $('#location_id').val();
              var transaction_date =   $('#transaction_date').val();
            //   var amount =   $('#amount').val();
            //   var payment_method = $('#payment_method option:selected').text();                              
              var debit_account_id =   $('#debit_account_id').val();
              var credit_account_id =   $('#credit_account_id').val();
              var payment_note =   $('#payment_note').val();
              var details_list = selectRow();
              var document_type =  '{{$document_type_code}}';

              var is_single =0;
              
              if($('#ad_as_single_entry').prop('checked')) {
                    is_single =1;
                } else {
                    is_single =0;
                }

             $('#btn_deposit').prop('disabled',true);
             $('#btn_deposit').html('<i class="fas fa-sync fa-spin"></i> Save (DEPOSIT)');
             

              $.ajax({
                    method: 'POST',
                    url: '/accounting/bank/deposit/store',
                    // dataType: 'JSON',
                    data: { 

                             transaction_date: transaction_date,                        
                             debit_account_id: debit_account_id, credit_account_id:credit_account_id,
                             payment_note: payment_note, details_list: details_list, is_single:is_single,                         
                        },
                    success: function(result) {
                         //console.log(result)
                         $('#btn_deposit').prop('disabled',false);
                         $('#btn_deposit').html('Save (DEPOSIT)');

                        if (result) {
                            if(result =='done'){
                                toastr.success('Transaction Successfully');
                                getTransactionData(credit_account_id,);
                                resetFormData();
                            }else{
                                toastr.error('Something went wrong');
                            }
                        }else{
                            toastr.error('Something went wrong');
                        }

                    },
                });
           




    }

    function resetFormData(){
         
            $('#transaction_date_error').html(null)
            $('#amount_error').html(null)
                          
            // $('#cr_account_id_error').html(null)
            $('#dr_account_id_error').html(null)

             
            
            $('#debit_account_id').val(null).select2();
            $('#credit_account_id').val(null);


            $('#transaction_date').val(null)
            

            $('#amount').val('0.00')
            $('#ad_as_single_entry').prop('checked',false);
            $('#select_all_checkbox').prop('checked',false);

            
            
            
            $('#total_adjusted').val('0.00')            
            $('#debtor_inv_list_body').html(null)
            $("#creditor_inv_list_body").html(null);

            $('#payment_note').val(null)
    }

    function validateData(){

        $('#rows_not_selected_error').html(null)
        $('#transaction_date_error').html(null)
        $('#dr_account_id_error').html(null)
        $('#rows_not_selected_error').html(null)

        var first_cheque_no = '';
        var count=0;
        $('#in_hand_tbody tr').each(function() {
            
            var checked = $(this).find(".transaction_id").prop('checked'); 

                if(checked){                     
                    if($(this).find('.payment_method').val()=='cheque'){
                        if(($(this).find('.cheque_no').val()==null) || ($(this).find('.cheque_no').val()=='')){
                            $(this).find('.cheque_no').addClass("bg-danger");  
                            $('#rows_not_selected_error').html('* Cheque numbers are cannot be empty. Please enter check numbers to continue.');
                            return false;                   
                        }else{

                            if(isNaN($(this).find('.cheque_no').val())){
                                $('#rows_not_selected_error').html('* Cheque numbers should be a number. Please enter check numbers to continue.');
                                $(this).find('.cheque_no').addClass("bg-danger"); 
                                return false;
                            }else{

                                if($('#ad_as_single_entry').prop('checked')){
                                    count++;
                                    if(count==1){
                                        first_cheque_no = $(this).find('.cheque_no').val();
                                    }

                                    if($(this).find('.cheque_no').val() != first_cheque_no){
                                        
                                        $(this).find('.cheque_no').addClass("bg-danger");
                                        $('#rows_not_selected_error').html('* Cheque numbers are does not match, Please try again later.');
                                        return false;
                                    }

                                }else{
                                    $(this).find('.cheque_no').removeClass("bg-danger");
                                }
                                
                                
                            }

                            
                        }
                    }

                }
            });

        if($('#amount').val()==0){
            $('#rows_not_selected_error').html('* Please Select Receipt to continue')
            return false
        }else if($('#transaction_date').val()==''){
            $('#transaction_date_error').html('* Required')
            return false
        }else if($('#debit_account_id').val()==''){
            $('#dr_account_id_error').html('* Please Select Receipt to continue')
            return false
        }

       

        // $('#rows_not_selected_error').html('* Cheque numbers are cannot be empty. Please enter check numbers to continue.')

        return true;
    }

    function getTransactionData(account_id,name=null) {
            
            $("#in_hand_tbody").html(null);	
            $("#credit_account_id").val(account_id);
            
            if(name!=null)	{
                $("#credit_account_name").val(name);	
            }
           
            var start = '';
                    var end = '';
                    if ($('#filter_date_range').val()) {
                                start = $('input#filter_date_range')
                                    .data('daterangepicker')
                                    .startDate.format('YYYY-MM-DD');
                                end = $('input#filter_date_range')
                                    .data('daterangepicker')
                                    .endDate.format('YYYY-MM-DD');
                    } 
            
            $('.view_btn_'+account_id).html('<i class="fas fa-sync fa-spin"></i>');
            $('.view_btn_'+account_id).prop('disabled',true);
			$.ajax({
				method: 'POST',
				url: `/accounting/bank/deposit/get/data`,
				dataType: 'html',
                data :{
                    account_id: account_id,
                    start_date: start,
                    end_date: end                
                },				
				success: function(result) {
					// console.log(result)
					if (result) {
                        $('.view_btn_'+account_id).html('View');
                        $("#in_hand_tbody").html(result);
					}else{
                        toastr.error('No Receipts found!');
                        $('.view_btn_'+account_id).html('View');
                    }

                    $('.view_btn_'+account_id).prop('disabled',false);

				},
			});
    }




</script>
<style>

input[type="checkbox"]{
    transform : scale(1.5);
}

.dp-table tbody td {
    padding-top: 1px !important; 
    padding-bottom: 1px !important;
    border: 1px solid #aaaaaa !important;
    vertical-align: middle !important;
    font-weight: 500;
}
.dp-table tbody th {
    padding-top: 3px;
    padding-bottom: 3px;
    border: 1px solid #aaaaaa;    
    vertical-align: middle !important;
}

.dp-table thead th {
    padding-top: 3px;
    padding-bottom: 3px;
    border: 1px solid #aaaaaa !important;  
    vertical-align: middle !important;
}

.dp-table tbody .balance th {
    padding: 8px;    
    
}
.dp-table tbody .bold-font {
    font-weight: bold;   
}

.dp-table .acc-type {
    background-color: rgb(232, 232, 232);
}

.dp-table .acc-category {
}

</style>

<style>
    .custom_table {
      font-family: Arial, Helvetica, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }
    
    .custom_table td, .custom_table th {
      border: 1px solid #ddd;
      padding: 2px;
    }
    .custom_table strong {
     padding-left: 5px;
    }
    
    .custom_table tr {background-color: #f2f2f2;}
    
    
    /* #customers tr:hover {background-color: #ddd;} */
    
    .custom_table th {
      padding-top: 12px;
      padding-bottom: 12px;
      text-align: center;
      background-color: #6b6b6b;
      color: white;
    }
    </style>

@endsection
