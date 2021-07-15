@extends('layouts.app')
@section('title', 'Profit & Loss Report')

@section('content')
@include('accounting::layouts.nav')

<!-- Main content -->
<section class="content no-print">
    <div class="row no-print">
        <div class="col-md-4">
            <h3>Profit & Loss Report</h3>
        </div>
        <div class="col-md-4 col-xs-12 mt-15 pull-right">
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
                {!! Form::text('date_of_report', null, ['class' => 'form-control', 'id'=> 'date_of_report', 'readonly', 'required']); !!}                                
            </div>                         
        </div>
    </div>
    <br>   
    <div class="row">                                                    
        <div class="col-sm-12">
                                    
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="trial_balance_table">
                    <thead>
                        <tr class="row-border blue-heading">
                            <th class="text-center" width="5%">-</th>
                            <th class="text-center" colspan="2" width="45%">DESCRIPTION</th>  
                            <th class="text-center" width="20%">CURRENT PERIOD / (%)</th>
                            <th class="text-center" width="5%"> (%)</th>  
                        </tr>
                    </thead>
                    <tbody id="trial_bl_tb_body">  
                        {!! $table_data !!}                        
                        {{-- <tr class="acc-type">
                            <th colspan="2">SALES REVENUE</th>
                            <th class="text-right" >12254</th>
                        </tr> 
                        <tr class="acc-type">
                            <th colspan="2">COST OF SALES</th>
                            <th class="text-right" >12254</th>
                        </tr>                         

                        <tr>
                            <td colspan="3">-</td>
                        </tr> 
                        <tr class="acc-type">
                            <td colspan="2"><b>GROSS PROFIT</b> &nbsp; &nbsp; <i>[ Sales - Cost of Sales ]</i></td>
                            <th colspan=""></th>
                        </tr>  

                        <tr>
                            <td colspan="3">-</td>
                        </tr>  
                        <tr class="acc-type">
                            <th colspan="2">Operating Expenses </th> 
                            <th class="text-right">2233.00</th>                            
                        </tr>     
                        <tr >
                            <td>-</td>
                            <td>exp 01</td>
                            <td class="text-right" >12254</td>
                        </tr>     
                        <tr >
                            <td>-</td>
                            <td>exp 02</td>
                            <td class="text-right" >12254</td>
                        </tr>  
                        <tr class="acc-type">
                            <th colspan="2">Non Operating Expenses </th>     
                            <th class="text-right">2233.00</th>                          
                        </tr>     
                        <tr >
                            <td>-</td>
                            <td>exp 01</td>
                            <td class="text-right" >12254</td>
                        </tr>     
                        <tr >
                            <td>-</td>
                            <td>exp 02</td>
                            <td class="text-right" >12254</td>
                        </tr> 

                        <tr>
                            <td colspan="3">-</td>
                        </tr> 
                        <tr class="acc-type">
                            <td colspan="2"><b>TOTAL EXPENSES</b> &nbsp; &nbsp; <i>[ Operating Expenses + Non Operating Expenses ]</i></td>
                            <th colspan=""></th>
                        </tr>
                        <tr>
                            <td colspan="3">-</td>
                        </tr>
                        <tr class="acc-type">
                            <td colspan="2"><b>INCOME FORM OPERATION</b>  &nbsp; &nbsp; <i>[ Gross Profit - Total Expenses ]</i> </td> 
                            <th class="text-right">2233.00</th>                            
                        </tr> 
                        <tr class="acc-type">
                            <th colspan="2">OTHER INCOME </th> 
                            <th class="text-right">2233.00</th>                            
                        </tr> 
                        <tr >
                            <td>-</td>
                            <td>inc 01</td>
                            <td class="text-right" >12254</td>
                        </tr> 
                        <tr >
                            <td>-</td>
                            <td>inc 02</td>
                            <td class="text-right" >12254</td>
                        </tr>
                        <tr>
                            <td colspan="3">-</td>
                        </tr>
                        <tr class="acc-type">
                            <td colspan="2"><b>NET PROFIT </b>  &nbsp; &nbsp; <i>[ Income Form Operation + Other Income ]</i> </td> 
                            <th class="text-right">2233.00</th>                            
                        </tr>  --}}


                             
                    </tbody>
                </table>
            </div>
        </div>
    </div>     
</section>

@stop

@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
       

        $('#date_of_report').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#date_of_report').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            }
        );

        $('#date_of_report').change( function() {            
            getPnlData() 
        });

        $('#location_id').change( function() {            
            getPnlData() 
        });

        

});
    function getPnlData() {
        
            $("#trial_bl_tb_body").html(null);	 

            var start = '';
            var end = '';
             

            if ($('#date_of_report').val()) {
                start = $('input#date_of_report')
                    .data('daterangepicker')
                    .startDate.format('YYYY-MM-DD');
                end = $('input#date_of_report')
                    .data('daterangepicker')
                    .endDate.format('YYYY-MM-DD');
            }
            console.log(start)
			$.ajax({
				method: 'POST',
				url: `/accounting/report/pnl/data`,
				// dataType: 'html',
                data :{start: start, end:end},				
				success: function(result) {
					 console.log(result)
					if (result) {
                        $("#trial_bl_tb_body").html(result);	
                       
					}else{
                        $("#trial_bl_tb_body").html(null);
                    }

				},
			});
        }




</script>
<style>
#trial_balance_table tbody td {
    padding-top: 1px;
    padding-bottom: 1px;
    border: 1px solid #aaaaaa;
}
#trial_balance_table tbody th {
    padding-top: 3px;
    padding-bottom: 3px;
    border: 1px solid #aaaaaa;
    
}

#trial_balance_table tbody .balance th {
    padding: 8px;    
    
}
#trial_balance_table tbody .bold-font {
    font-weight: bold;   
}

#trial_balance_table .acc-type {
    background-color: rgb(232, 232, 232);
}

#trial_balance_table .acc-type-sub {
    background-color: rgb(255, 255, 255);
}

#trial_balance_table .acc-category {

}

</style>

@endsection
