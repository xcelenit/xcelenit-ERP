@extends('layouts.app')
@section('title', 'Trial Balance')

@section('content')
@include('accounting::layouts.nav')

<!-- Main content -->
<section class="content no-print">
    <div class="row no-print">
        <div class="col-md-4">
            <h3>Trial Balance</h3>
        </div>
        <div class="col-md-4 col-xs-12 mt-15 pull-right">
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
                {!! Form::text('date_of_report', @format_date('now'), ['class' => 'form-control', 'id'=> 'date_of_report', 'readonly', 'required']); !!}                                
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
                            <th class="text-center" colspan="2" width="5%">TYPE</th>
                            <th class="text-center" width="10%">CODE</th>
                            <th class="text-center" width="45%">ACCOUNT DESCRIPTION</th>                                                 
                            <th class="text-center" width="20%">DEBIT</th>
                            <th class="text-center" width="20%">CREDIT</th>                                                           
                        </tr>
                    </thead>
                    <tbody id="trial_bl_tb_body">                                            
                        {!! $table_data !!}                                           
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
       
    $('#date_of_report').datepicker({
            autoclose: true,
            format: datepicker_date_format
        });

        $('#date_of_report').change( function() {
            
            var date = $('#date_of_report').val();
            getTrialBalanceData(date)   
            
        });

});
    function getTrialBalanceData(date) {
        
            $("#trial_bl_tb_body").html(null);	 
        
			$.ajax({
				method: 'POST',
				url: `/accounting/report/trial-balance/data`,
				dataType: 'html',
                data :{rp_date: date},				
				success: function(result) {
					//  console.log(result)
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

#trial_balance_table .acc-category {

}

</style>

@endsection
