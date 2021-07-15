@extends('layouts.app')
@section('title', 'Bank Reconcilation')

@section('content')
@include('accounting::layouts.nav')

<!-- Main content -->
<section class="content no-print">
    <div class="row no-print">
        <div class="col-sm-8">
            <h3>BANK RECONCILATION</h3>
        </div>
        <div class="col-md-4 col-xs-12 mt-15 pull-right">
            
        </div>
    </div>
    <br>   
    @php
        
    @endphp
    @component('components.widget', ['class' => 'box-solid'])
    <div class="row"> 
        <div class="col-sm-12 col-md-12"> 
            <table class="custom_table">
                <tbody>
                   
                    <tr>
                        <td class="col-sm-2 text-left">
                            <strong class="">ACCOUNT :</strong>
                        </td>  
                        <td colspan="2" class="col-sm-6" > 
                            {!! Form::select('account_id', $account_list, null, ['id' => 'account_id' ,'class' => 'form-control select2', 'required']); !!}
                            
                        </td>       
                        <td class="col-sm-4 text-center">
                            <span> No of <b id="total_no_of_to_be_rec">0</b> Entries to be Reconcile in this Account</span>
                        </td>                    
                    </tr>

                    <tr>
                        <td class="col-sm-2 text-left">
                            <strong class="">YEAR :</strong>
                        </td>  
                        <td class="col-sm-4" > 
                            {!! Form::select('year', $years, null, ['id' => 'year' ,'class' => 'form-control select2', 'required']); !!}                                
                        </td>  
                        <td class="col-sm-2 text-left">
                            <strong class="">MONTH :</strong>
                        </td>  
                        <td class="col-sm-4"> 
                            {!! Form::select('month', $months, null, ['id' => 'month' ,'class' => 'form-control select2', 'required']); !!}                               
                        </td>                    
                    </tr>
                    <tr>
                        <td class="col-sm-2 text-left">
                            <strong class="">DEBIT / CREDIT :</strong>
                        </td>  
                        <td class="col-sm-4" >                           
                            <div style="margin-left: 15px;" class="radio mt-0 mb-0">
                                <label>
                                  <input type="radio" id="entry_type" name="entry_type" checked autocomplete="false" value="DR" class="" > &nbsp;&nbsp;&nbsp;Only Debit
                                </label>                                   
                            </div>
                            <div style="margin-left: 15px;" class="radio mt-0 mb-0">
                                <label>
                                    <input type="radio" id="entry_type" name="entry_type"  autocomplete="false" value="CR" class="" > &nbsp;&nbsp;&nbsp;Only Credit
                                </label>
                            </div>
                        </td>
                        <td class="col-sm-2">
                            <strong class="">STATUS :</strong>
                        </td>
                        <td class="col-sm-4 text-left">
                            <div style="margin-left: 15px;" class="radio mt-0 mb-0">
                                <label>
                                  <input type="radio" id="entry_status" name="entry_status" autocomplete="false" value="1" class="" > &nbsp;&nbsp;&nbsp;Realized
                                </label>                                   
                            </div>
                            <div style="margin-left: 15px;" class="radio mt-0 mb-0">
                                <label>
                                    <input type="radio" id="entry_status" name="entry_status"  autocomplete="false" value="0" class="" > &nbsp;&nbsp;&nbsp;Non Realized
                                </label>
                            </div>
                            <div style="margin-left: 15px;" class="radio mt-0 mb-0">
                                <label>
                                    <input type="radio" id="entry_status" name="entry_status" checked autocomplete="false" value="all" class="" > &nbsp;&nbsp;&nbsp;All
                                </label>
                            </div>
                        </td> 
                    </tr>
                   
                    <tr>                           
                        <td colspan="4" style="padding: 5px;" class="col-sm-12"> 
                            <span id="rows_not_selected_error" class="text-danger"></span>
                             <input type="hidden" value="0" id="has_emt_cheque_no">
                            <button type="button" id="btn_view" style="margin-left: 10px" onclick="getTransactionData()" class="btn btn-primary pull-right">VIEW ACCOUNT TRANSACTIONS</button>                                 
                        </td>                                                
                    </tr>                        
                </tbody>
            </table> 
        </div>                                               
        <div class="col-sm-12 col-md-12"> 
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified">
                    <li class="active">
                        <a href="#bank_rec" data-toggle="tab" aria-expanded="true"><i class="fas fa-scroll" aria-hidden="true"></i> &nbsp;&nbsp; Reconcilation</a>
                    </li>
                    <li class="">
                        <a href="#bank_bl" data-toggle="tab" aria-expanded="true"><i class="fas fa-calculator" aria-hidden="true"></i> &nbsp;&nbsp; Balance</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bank_rec">
                        <div class="s">
                            
                            <table class="table table-bordered dp-table">
                                <thead>                                              
                                    <tr class="bg-danger">
                                        <th width="15%" class="text-center">TRN DATE</th>
                                        <th class="text-center">DOC NO</th>
                                        <th class="text-center">DESCRIPTION - VENDOR NAME (PARTY)</th>
                                        <th class="text-center">CHEQUE NO</th>
                                        <th class="text-center">DEBIT AMT</th>
                                        <th class="text-center">CREDIT AMT</th>
                                        <th width="3%" class="text-center">
                                            SELECT                                 
                                        </th>                            
                                        <th class="text-center">PRD</th>                            
                                        <th class="text-center">TYPE</th>
                                        <th class="text-center">RECONCILE AT</th>                             
                                    </tr>
                                </thead>
                                <tbody id="rec_tbody">                
                                    
                                </tbody>
                                <tfoot>                         
                                    <tr  class="bg-warning">
                                        <th class="text-right" colspan="4"> TOTAL SELECTED:</th>
                                        <th colspan="2" class="text-right" id="total_selected_val"></th>                             
                                        <th  class="text-right" colspan="4">
                                            <button type="button" id="btn_update" style="margin-left: 10px" onclick="storeData()" class="btn btn-success pull-right">UPDATE</button>     
                                        </th>  
            
                                    </tr>                      
                                </tfoot>
                            </table>
            
                           
            
                            
                        </div>
                    </div>
                    <div class="tab-pane" id="bank_bl">
                        <div class="s">
                            
                            <table class="table table-bordered dp-table">
                                <thead class=""> 
                                    <tr class="bg-primary">
                                        <th class="text-center" colspan="5"> CLOSING BALANCE (BOOK) :</th>
                                        <th width="15%" class="text-right" id="closing_balance" ></th>              
                                    </tr>     
                                    <tr class="bg-bl">
                                        <th class="text-center" colspan="5"> NOT REALISED </th>                                                   
                                    </tr>                                           
                                    <tr class="bg-danger">
                                        <th width="15%" class="text-center">TRN DATE</th>
                                        <th width="10%" class="text-center">DOC NO</th>
                                        <th class="text-center">DESCRIPTION - VENDOR NAME (PARTY)</th>
                                        <th width="10%" class="text-center">CHEQUE NO</th>
                                        <th width="15%" class="text-center">AMOUNT</th>                                                                      
                                    </tr>
                                </thead>
                                <tbody id="bl_dr_tbody">  
                                </tbody>
                                <thead>
                                    <tr  class="bg-warning">
                                        <th class="text-right" colspan="3"> TOTAL NOT REALISED :</th>
                                        <th class="text-center" id="not_realised_count"></th>              
                                        <th class="text-right" id="not_realised_total"></th>              
                                    </tr> 
                                    <tr class="bg-bl">
                                        <th class="text-center" colspan="5"> NOT PRESENTED + RETURNED CHEQUES </th>                                                  
                                    </tr>  
                                </thead>
                                <thead>                                              
                                    <tr class="bg-danger">
                                        <th width="12%" class="text-center">TRN DATE</th>
                                        <th class="text-center">DOC NO</th>
                                        <th class="text-center">DESCRIPTION - VENDOR NAME (PARTY)</th>
                                        <th class="text-center">CHEQUE NO</th>
                                        <th class="text-center">AMOUNT</th>                                                                      
                                    </tr>
                                </thead>
                                <tbody id="bl_cr_tbody">                
                                    
                                </tbody>
                                <thead>
                                    <tr  class="bg-warning">
                                        <th class="text-right" colspan="3"> TOTAL NOT PRESENTED + RETURNED CHEQUE :</th>
                                        <th class="text-center" id="not_presented_count"></th>              
                                        <th class="text-right" id="not_presented_total"></th>              
                                    </tr>  
                                    <tr  class="bg-primary">
                                        <th class="text-center" colspan="5"> CLOSING BALANCE (BANK) :</th>
                                        <th colspan="6" class="text-right" id="bank_balance"></th>              
                                    </tr> 
                                </thead>
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>                                   
            
        </div>
    </div>  
    @endcomponent   
</section>

@stop

@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
        
});

    function selectRow(){
        
        var total_amount = 0;
        var data_array = []
        
         $('#rec_tbody tr').each(function() { 
                
                var ledger_transation_id =$(this).find(".transaction_id").val();

                if(($(this).find(".transaction_id").prop('checked'))){
                    $(this).addClass("bg-info");

                    var val = 0;
                    var isUpdateAmount = 0;
                    if((parseFloat($(this).find(".amount").val()) < 2) && ($(this).find(".cr_amount").val()!=undefined)){
                        
                        if(($(this).find(".cr_amount").val())!=''){
                            val = parseFloat($(this).find(".cr_amount").val());  
                            isUpdateAmount =1;                          
                        }
                        total_amount += val;
                    }else{
                        total_amount += parseFloat($(this).find(".amount").val());
                    }
                    
                                        
                    data_array.push({ledger_transation_id: ledger_transation_id, total_amount: val, is_update_amount: isUpdateAmount , is_reconcile: 1});

                }else{
                    $(this).removeClass("bg-info");
                    $(this).find('.cheque_no').removeClass("bg-danger");
                    
                    data_array.push({ledger_transation_id: ledger_transation_id, is_update_amount: 0, is_reconcile: 0});
                }
         });

        // console.log(total_amount.toFixed(2));
         $('#total_selected_val').html(total_amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"))
        //  $('#amount').val(total_amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,"));

         return data_array;
    }

    function storeData(){
                
              var rows = selectRow();

              if(rows.length>0){

                $('#btn_update').prop('disabled',true);
                $('#btn_update').html('<i class="fas fa-sync fa-spin"></i> &nbsp;&nbsp;&nbsp; UPDATE');
                var year =  $("#year").val();
                var month =  $("#month").val();

                $.ajax({
                        method: 'POST',
                        url: '/accounting/bank/reconcilation/store',
                        // dataType: 'JSON',
                        data: { 
                                year:year,
                                month:month,
                                rows:rows
                            },
                        success: function(result) {
                            // console.log(result)
                            $('#btn_update').prop('disabled',false);
                            $('#btn_update').html('UPDATE');

                            if (result) {
                                if(result =='done'){
                                    getTransactionData();
                                    toastr.success('Transaction Successfully');                                
                                }else{
                                    toastr.error('Something went wrong');
                                }
                            }else{
                                toastr.error('Something went wrong');
                            }

                        },
                    });
              } 
              
    }
    

    function getTransactionData() {
            
            $("#rec_tbody").html(null);	
            $("#bl_cr_tbody").html(null);	
            $("#bl_dr_tbody").html(null);	
            $("#total_no_of_to_be_rec").html(0);
            $("#not_presented_count").html(0.00);
            $("#not_presented_total").html(0.00);
            $("#not_realised_count").html(0.00);
            $("#not_realised_total").html(0.00);

            var account_id =  $("#account_id").val();
            var year =  $("#year").val();
            var month =  $("#month").val();
            var entry_type =  $("input[name='entry_type']:checked").val(); 
            var status =  $("input[name='entry_status']:checked").val(); 

           
            $('#btn_view').html('<i class="fas fa-sync fa-spin"></i> &nbsp;&nbsp;&nbsp; VIEW ACCOUNT TRANSACTIONS');
            $('#btn_view').prop('disabled',true);
			$.ajax({
				method: 'POST',
				url: `/accounting/bank/reconcilation/getrecdata`,
				// dataType: 'JSON',
                data :{
                    account_id: account_id,
                    year: year,
                    month: month,                
                    entry_type: entry_type,                
                    status: status,                
                },				
				success: function(result) {
					//console.log(result)
					if (result.status==200) {
                        $('#btn_view').html('VIEW ACCOUNT TRANSACTIONS');
                        $("#rec_tbody").html(result.rec_data);
                        $("#total_no_of_to_be_rec").html(result.total_no_of_to_be_rec);

                        $("#bl_dr_tbody").html(result.not_realized);
                        $("#not_realised_count").html(result.not_realized_count);
                        $("#not_realised_total").html(result.not_realized_total);
                        
                        $("#bl_cr_tbody").html(result.not_presented);
                        $("#not_presented_count").html(result.not_presented_count);
                        $("#not_presented_total").html(result.not_presented_total);

                        $("#closing_balance").html(result.closing_bl);
                        $("#bank_balance").html(result.bank_balance);


					}else{
                        toastr.error('No Records found!');
                        $('#btn_view').html('VIEW ACCOUNT TRANSACTIONS');
                    }

                    $('#btn_view').prop('disabled',false);

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

.bg-bl{
    background-color: rgb(194, 192, 192);
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
