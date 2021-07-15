@extends('layouts.app')
@section('title', 'Acconting')
 
@section('content')
@include('accounting::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{$doc_type['type']}}</h1>
</section>

@php
    $transaction_id =null;
    if(isset($transaction)){
        $transaction_id = $transaction->id;
    }
@endphp

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-solid'])
    {!! Form::open(['url' => action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountController@store'), 'method' => 'post', 'id' => 'account_form', 'files' => true ]) !!}
    
    <div class="row">
        <div class="col-md-12">
                       
            <table class="custom_table">
                <tbody>
                    <tr>
                        <td class="col-sm-2 text-left">
                            <strong class="">DOCUMENT TYPE :</strong>
                        </td> 
                        @if(isset($transaction))
                            <td class="col-sm-2">                            
                                {!! Form::text('doc_type', $doc_type['code'], ['placeholder' => '', 'class' => 'form-control','required','disabled']); !!}
                            </td>
                            <td class="col-sm-2 text-left">
                                <strong class="">DOCUMENT NO :</strong>
                            </td> 
                            <td  class="col-sm-2">                            
                                {!! Form::text('doc_type', $transaction->document_no, ['placeholder' => '', 'class' => 'form-control','required','disabled']); !!}
                            </td>
                        @else
                        <td colspan="3" class="col-sm-6">                            
                            {!! Form::text('doc_type', $doc_type['code'], ['placeholder' => '', 'class' => 'form-control','required','disabled']); !!}
                        </td>
                        @endif
                        
                        <td class="col-sm-2 text-left">
                            <strong class="">PERIOD :</strong>
                        </td> 
                        <td class="col-sm-2">                            
                            {!! Form::text('period', isset($transaction) ? $transaction->period : $current_fy_period, ['placeholder' => '', 'class' => 'form-control','required','disabled']); !!}
                        </td>
                    </tr>  
                    <tr>                     
                        <td class="col-sm-2 text-left bg-danger">
                            <strong class="">SUPPLIER NAME :</strong>
                        </td>  
                        <td colspan="3" class="col-sm-6">
                            {!! Form::select('vendor_id', $contact_dropdown, isset($transaction) ? $transaction->vendor_id : null, ['id' => 'vendor_id' ,'class' => 'form-control select2', 'disabled', 'required']); !!}
                            <span id="vendor_id_error" class="text-danger"></span>

                        </td>
                        <td class="col-sm-2">
                            <strong class="">LOCATION :</strong>                            
                        </td>
                        <td class="col-sm-2">
                            {!! Form::select('location_id', $locations, isset($transaction) ? $transaction->location_id : null, ['id' => 'location_id' ,'class' => 'form-control select2']); !!}                           
                        </td>
                         
                    </tr>
                    
                    
                </tbody>
            </table>    
            <br>
            <table class="custom_table">
                <tbody>
                    <tr>
                        <td class="col-sm-2 text-left">
                            <strong class="">TRN. DATE :</strong>
                        </td> 
                        <td class="col-sm-2">                            
                            {{-- {!! Form::text('doc_type', '', ['placeholder' => '', 'class' => 'form-control','required']); !!} --}}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('transaction_date', isset($transaction) ? $transaction->transaction_date : null, ['class' => 'form-control', 'id'=> 'transaction_date', 'readonly', 'required']); !!}                                
                            </div>
                            <span id="transaction_date_error" class="text-danger"></span>
                        </td>

                        <td class="col-sm-2 text-left">
                            <strong class="">PAYMENT METHOD :</strong>
                        </td> 
                        <td class="col-sm-2">                            
                            {{-- {!! Form::select('payment_method', $payment_method_array, 4, ['id' => 'payment_method' ,'class' => 'form-control select2', 'required']); !!} --}}
                            <select name="payment_method" class="form-control" id="payment_method" disabled>
                                @foreach ($payment_method_array as $item)
                                     {{-- <option value="{{$item['default_account_id']}}">{{$item['name']}}</option> --}}
                                     <option @if(isset($transaction))  @if($transaction->payment_method == strtolower($item['name'])) selected @endif  @endif value="{{$item['default_account_id']}}">{{$item['name']}}</option>
                                @endforeach
                            </select>
                        </td>

                        <td class="col-sm-2 text-left">
                            <strong class="">AMOUNT :</strong>
                        </td> 
                        <td class="col-sm-2">
                            {!! Form::number('amount', isset($transaction) ? $transaction->total_amount : null, ['placeholder' => '','id'=>'amount', 'class' => 'form-control text-right','required']); !!}
                            <span id="amount_error" class="text-danger"></span>
                        </td>
                    </tr>  
                         
                </tbody>
            </table>
            <table id="cheque_data_table" class="custom_table @if($doc_type['code']!='CPV') hide @endif">
                <tbody>                   
                    <tr>
                        <td class="col-sm-2 text-left">
                            <strong class="">CHEQUE NO :</strong>
                        </td> 
                        <td class="col-sm-2">                            
                            {!! Form::text('cheque_no', isset($transaction) ? $transaction->cheque_no : null, ['placeholder' => '', 'class' => 'form-control','id'=>'cheque_no','required']); !!}
                            <span id="cheque_no_error" class="text-danger"></span>

                        </td>
                        <td class="col-sm-2 text-left">
                            <strong class="">CHEQUE DATE :</strong>
                        </td> 
                        <td class="col-sm-2"> 
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('cheque_date', isset($transaction) ? $transaction->cheque_date : null, ['class' => 'form-control', 'id'=> 'cheque_date', 'readonly', 'required']); !!}
                            </div>
                            <span id="cheque_date_error" class="text-danger"></span>
                        </td>  
                        
                        <td class="col-sm-4">
                            
                        </td>                       
                    </tr>     
                    <tr>
                        <td class="col-sm-2 text-left">
                            <strong class="">PAYEE :</strong>
                        </td> 
                        <td colspan="3" class="col-sm-6"> 
                            <select name="payee" class="form-control select2" id="payee">
                                <option value="CASH">CASH</option>
                            </select>
                        </td>
                        <td class="col-sm-4">
                            
                        </td> 

                    </tr>             
                </tbody>
            </table> 
            <table class="custom_table">
                <tbody>
                    
                    <tr>
                        <td class="col-sm-2 text-left bg-success">
                            <strong class="">DEBIT ACCOUNT :</strong>
                        </td>  
                        <td class="col-sm-6" >                           
                            {!! Form::select('debit_account_id', $debit_account_list, isset($transaction) ? $transaction->debit_account_id : null, ['id' => 'debit_account_id' ,'class' => 'form-control select2', 'required']); !!}
                            <span id="dr_account_id_error" class="text-danger"></span>
                        </td>
                        <td class="col-sm-4">

                        </td>
                    </tr> 
                    <tr>
                        <td class="col-sm-2 text-left bg-success">
                            <strong class="">CREDIT ACCOUNT :</strong>
                        </td>  
                        <td class="col-sm-6" >                           
                            {!! Form::select('account_id', $credit_account_list, isset($transaction) ? $transaction->credit_account_id : null, ['id' => 'account_id' ,'class' => 'form-control select2', 'required']); !!}
                            <span id="account_id_error" class="text-danger"></span>
                        </td>
                        <td class="col-sm-4">

                        </td>
                    </tr> 
                </tbody>
            </table>

            <br>
            <table class="custom_table">
                <tbody>                   
                    <tr>
                        <td class="col-sm-2 text-left">
                            <strong class="">PAYMENT NOTE :</strong> (Max 255)
                        </td> 
                        <td colspan="5" class="col-sm-10">                            
                            {!! Form::textarea('payment_note', isset($transaction) ? $transaction->payment_note : null, ['placeholder' => '','rows' => 1, 'cols' => 54, 'id'=>'payment_note', 'class' => 'form-control','required']); !!}
                        </td>                                                
                    </tr>                  
                </tbody>
            </table> 
            <table class="custom_table">
                <tbody>                   
                    <tr>
                        <td style="padding: 5px;" class="col-sm-4 text-left p-5">
                            <strong style="text-transform: uppercase">Please select Invoices to be settele</strong>
                        </td> 

                        <td style="padding: 5px;" class="col-sm-2 text-center">
                            <strong>TOTAL AMOUNT ADJUSTED:</strong>
                        </td>
                        <td style="padding: 5px;" class="col-sm-2 text-center">
                            <strong id="tot_ad_lbl">0.00</strong>
                            <input type="hidden" id="error_in_ad" value="0" >
                        </td>
                        <td style="padding: 5px;" class="col-sm-2">  
                            <button type="button" style="margin-left: 10px" onclick="storeData()" class="btn btn-primary pull-right">Save</button>
                            <button type="button" onclick="storeData()" class="btn btn-success pull-right">Save & Print</button> 
                        </td>                                                
                    </tr>                  
                </tbody>
            </table> 
        </div> 
    </div>
    <br>
    
    <div class="row">
        <div class="col-sm-12">            
            <table  class="custom_table">
                <thead>
                    
                    <tr>
                        <th>Date</th>
                        <th>INV NO.(Ref No)</th>
                        <th>Location</th>
                        <th>PMT Status</th>
                        <th>INV AMT</th>
                        <th>SETTLED AMT</th>
                        <th>BAL AMT</th>
                        <th colspan="2">ADJUSTED</th>
                    </tr>
                </thead>
                <tbody id="inv_list_body">

                </tbody>
                <tfoot>
                   <tr>
                    <td class="text-right" colspan="8"> <strong>TOTAL AMOUNT ADJUSTED:</strong> </td>
                    <td>
                         {!! Form::text('total_adjusted', '', ['placeholder' => '', 'id'=>'total_adjusted', 'class' => 'form-control','required','disabled']); !!}
                    </td>
                   </tr>
                </tfoot>
            </table>
        </div>
    </div>
 
{!! Form::close() !!}
    @endcomponent
</section>
<!-- /.content -->


@stop
@section('javascript')
    {{-- @include('accounting::layouts.partials.common_script') --}}
    <script>
        $(document).on('change', '#vendor_id', function() {
			var vendor_id = $('#vendor_id').val(); 
            getDueInvoice(vendor_id);
            //getSupplierPayee(vendor_id);
            
        });

        $(document).on('change', '.adjusted', function() {			
            calculateAdjustedInvoice()
        });

        $(document).on('change', '.transaction_id', function() {			
            checkAdjustment()
            calculateAdjustedInvoice()
        });
        
        $(document).on('change', '#payment_method', function() {			
            
             if($('#payment_method').val()==5){
                $("#cheque_data_table").removeClass("hide");
             }else{               
                $("#cheque_data_table").addClass("hide");
             }

             $('#account_id').val($('#payment_method').val()).select2();
        });
        

        $(document).ready(function(){
            var transaction_id = '{{$transaction_id}}';
            if(transaction_id!=''){
                var vendor_id = $('#vendor_id').val(); 
                $("#vendor_id").prop( "disabled", false );
                getDueInvoice(vendor_id);
            }
            
        })


        $(document).on('change', '#debit_account_id', function() {
             
             var debit_account_id = $('#debit_account_id').val();
              
             $('#vendor_id').val(null).select2();
             $('#inv_list_body').html(null);             
              
             if(debit_account_id=='{{$creditor_account_id}}'){
                 $("#vendor_id").prop( "disabled", false );
                  
              }else{
                 $('#vendor_id').prop( "disabled", true );
                  
              }
         });

        

        //Datetime picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
        date: '{{(isset($transaction) ? $transaction->transaction_date : '')}}'
    });

    $('#cheque_date').datetimepicker({
        format: moment_date_format,
        ignoreReadonly: true,
        date: '{{(isset($transaction) ? $transaction->cheque_date : '')}}'
    });

        function getDueInvoice(vendor_id) {
            $("#inv_list_body").html(null);	 
            var transaction_id  = '{{$transaction_id}}';

			$.ajax({
				method: 'POST',
				url: '/accounting/payment/creditor/get-due-invoice',
				// dataType: 'html',
				data: { vendor_id: vendor_id, transaction_id:transaction_id },
				success: function(result) {
					 console.log(result)
					if (result) {
                        $("#inv_list_body").html(result.inv);	
                        $("#payee").html(result.payee);	
                        calculateAdjustedInvoice();
					}else{
                        $("#inv_list_body").html(null);
                    }

				},
			});
        }
        function getSupplierPayee(vendor_id) {
            $("#inv_list_body").html(null);	 

			$.ajax({
				method: 'POST',
				url: '/accounting/payment/get/creditor/payee',
				dataType: 'html',
				data: { vendor_id: vendor_id },
				success: function(result) {
					//  console.log(result)
					if (result) {
                        $("#payee").html(result);	
                        calculateAdjustedInvoice();
					}else{
                        $("#payee").html(null);
                    }

				},
			});
        }

        function storeData(){
           
          var isValidated = validatingData();
           if(isValidated==false){             
            return null
           }
           
           if($('#error_in_ad').val()==1){
                alert('Something went wrong in invoice settlement please check and try again')
            }else{
              var transaction_id  = '{{$transaction_id}}';
              var vendor_id =   $('#vendor_id').val();
              var location_id =   $('#location_id').val();
              var transaction_date =   $('#transaction_date').val();
              var amount =   $('#amount').val();
              var payment_method = $('#payment_method option:selected').text();
              var cheque_no =   $('#cheque_no').val();
              var cheque_date =   $('#cheque_date').val();
              var payee =   $('#payee').val();
              var account_id =   $('#account_id').val();
              var debit_account_id =   $('#debit_account_id').val();
              
              var payment_note =   $('#payment_note').val();
              var details_list = calculateAdjustedInvoice();
              var document_type =  '{{$doc_type['code']}}';
              

            //  var total_adjusted_val = $('#total_adjusted').val();

            //   if(parseFloat(total_adjusted_val)!=parseFloat(amount)){
            //         alert('Adjusted value dos not match with Paying Value. Please check and try again')
            //     return null;
            //   }    

                $.ajax({
                    method: 'POST',
                    url: '/accounting/payment/creditor/store',
                    // dataType: 'JSON',
                    data: { 
                        transaction_id: transaction_id,
                        debit_account_id: debit_account_id,
                        vendor_id: vendor_id , transaction_date: transaction_date,                        
                        payment_method: payment_method, cheque_no: cheque_no, payee:payee,
                        cheque_date: cheque_date, account_id: account_id,
                        payment_note: payment_note, details_list: details_list,
                        location_id: location_id, amount: amount,document_type: document_type
                        },
                    success: function(result) {
                        //  console.log(result)
                        if (result) {
                            if(result =='done'){
                                toastr.success('Transaction Successfully');
                                resetFormData();
                            }else{
                                toastr.success('Something went wrong');
                            }
                        }else{
                            toastr.success('Something went wrong');
                        }

                    },
                });

            }

             
        }
        
        function resetFormData(){
            $('#vendor_id_error').html(null)
            $('#transaction_date_error').html(null)
            $('#amount_error').html(null)
            $('#cheque_no_error').html(null)
            $('#cheque_date_error').html(null)
            $('#account_id_error').html(null)
            $('#dr_account_id_error').html(null)


            $('#vendor_id').val(null).select2();
            $('#location_id').val(null).select2();
            $('#account_id').val(null).select2();
            $('#debit_account_id').val(null).select2();


            $('#transaction_date').val(null)
            $('#payment_method').val(4)

            $('#amount').val(null)
            $('#cheque_no').val(null)
            $('#cheque_date').val(null)
            
            $('#tot_ad_lbl').html('0.00')
            $('#total_adjusted').val('0.00')            
            $('#inv_list_body').html(null)
            $('#payment_note').val(null)

        }

        function validatingData(){

            $('#vendor_id_error').html(null)
            $('#transaction_date_error').html(null)
            $('#amount_error').html(null)
            $('#cheque_no_error').html(null)
            $('#cheque_date_error').html(null)
            $('#account_id_error').html(null)


            if($('#vendor_id').val()==''){

                var debit_account_id = $('#debit_account_id').val();
                if(debit_account_id=='{{$creditor_account_id}}'){
                    $('#vendor_id_error').html('Required')
                     return false            
                }                
            }
            
            if($('#transaction_date').val()==''){
                $('#transaction_date_error').html('Required')
                return false
            }else if($('#amount').val()==''){
                $('#amount_error').html('Required')
                return false
            }else if($('#amount').val() < 0){
               $('#amount_error').html('Negative values not allowed')
               return false
            }else if($('#account_id').val()==''){
                $('#account_id_error').html('Required')
                return false
            } else if($('#debit_account_id').val()==''){
                $('#dr_account_id_error').html('Required')
                return false
            }  

            if($('#payment_method').val()==5) 
            {
                if($('#cheque_no').val()==''){
                    $('#cheque_no_error').html('Required')
                    return false
                }else if($('#cheque_date').val()==''){
                    $('#cheque_date_error').html('Required')
                    return false
                }
            }


            return true;

            
             
            
        }
        
        function calculateAdjustedInvoice(){

            var total_adjusted =0;
            var total_paying = parseFloat($('#amount').val());
            var data_array = [];

            if(isNaN(total_paying)){
                total_paying=0;
            }

            $('#inv_list_body tr').each(function() {

                var isAdjusted = $(this).find(".transaction_id").prop('checked'); 
                
                if(isAdjusted){
                    // var isNum = $(this).find(".adjusted").val();
                    // if(isNaN(isNum)==false){
                                            
                        total_adjusted += parseFloat($(this).find(".adjusted").val())

                        var due = $(this).find(".balance_amount").val();
                        if(parseFloat($(this).find(".adjusted").val())>due){
                            $(this).find(".adjusted").addClass("bg-red")
                            $('#error_in_ad').val(1);
                        }else{
                            $(this).find(".adjusted").removeClass("bg-red")
                            $('#error_in_ad').val(0);
                        }

                        if(total_adjusted>total_paying)
                        {
                            $("#tot_ad_lbl").addClass("bg-red");
                            $("#total_adjusted").addClass("bg-red");
                            $('#error_in_ad').val(1);
                        }else{
                            $("#tot_ad_lbl").removeClass("bg-red");
                            $("#total_adjusted").removeClass("bg-red");
                            if($('#error_in_ad').val()!=1){
                                $('#error_in_ad').val(0);
                            }
                        }

                    // }else{
                    //     $(this).find(".adjusted").addClass("bg-red")
                    //         $('#error_in_ad').val(1);
                    // }
                    data_array.push({
                        perent_transaction_id: parseInt($(this).find(".transaction_id").val()), 
                        sub_amount: $(this).find(".adjusted").val(), 
                        ref_no: $(this).find(".ref_no").val(), 
                        desc: $(this).find(".desc").val(),                        
                        })
                }

                           
                                
            });

            $('#tot_ad_lbl').html(total_paying.toFixed(2)+' - '+ total_adjusted.toFixed(2)+' = '+ (total_paying-total_adjusted).toFixed(2))
            $('#total_adjusted').val(total_adjusted.toFixed(2))

            return  data_array;

        }

        function checkAdjustment(){
            $('#inv_list_body tr').each(function() {

                var isAdjusted = $(this).find(".transaction_id").prop('checked'); 
                var total_adjusted = $('#total_adjusted').val();
                var total_paying = $('#amount').val();

                if(isAdjusted){                    
                        $(this).find(".adjusted").prop( "disabled", false );
                        
                        if(parseFloat($(this).find(".adjusted").val())==0){
                            
                            

                            var due = parseFloat($(this).find(".balance_amount").val());
                             
                            if(total_adjusted < total_paying){
                                
                                var av_paying_bl = (total_paying - total_adjusted);

                                if(av_paying_bl>=due){
                                    $(this).find(".adjusted").val(due.toFixed(2)) 
                                }else{
                                    $(this).find(".adjusted").val(av_paying_bl.toFixed(2)) 
                                }
                            }


                               
                        }
                                       
                }else{
                    $(this).find(".adjusted").prop( "disabled", true );
                    $(this).find(".adjusted").val(0.00)
                }
            });
        }
        

        

    </script>
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
