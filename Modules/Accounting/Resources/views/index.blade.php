@extends('layouts.app')
@section('title', 'Acconting')
  
@section('content')
@include('accounting::layouts.nav')
<!-- Content Header (Page header) -->
{{-- <section class="content-header">
    <h1>Dashboard</h1>
</section> --}}
<section class="content-header content-header-custom">
    <h1>
        Account Dashboard
    </h1>
</section>

<!-- Main content -->
<section class="content content-custom no-print">
 <br>  
<div class="row">
    <div class="col-md-4 col-xs-12">
      {{-- @if(count($all_locations) > 1)
        {!! Form::select('dashboard_location', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_location'), 'id' => 'dashboard_location']); !!}
      @endif --}}
    </div>
		<div class="col-md-8 col-xs-12">
			<div class="btn-group pull-right" data-toggle="buttons">
				<label class="btn btn-info active">
    				<input type="radio" name="date-filter"
    				data-start="{{ date('Y-m-d') }}" 
    				data-end="{{ date('Y-m-d') }}"
    				checked> {{ __('home.today') }}
  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="{{ $date_filters['this_week']['start']}}" 
    				data-end="{{ $date_filters['this_week']['end']}}"
    				> {{ __('home.this_week') }}
  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="{{ $date_filters['this_month']['start']}}" 
    				data-end="{{ $date_filters['this_month']['end']}}"
    				> {{ __('home.this_month') }}
  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter" 
    				data-start="{{ $date_filters['this_fy']['start']}}" 
    				data-end="{{ $date_filters['this_fy']['end']}}" 
    				> {{ __('home.this_fy') }}
  				</label>
            </div>
        </div>
</div>
    <br>
    
<div class="row row-custom">
    	<div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
	        <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>

	        <div class="info-box-content">
	          <span class="info-box-text">Total Income</span>
	          <span class="info-box-number total_income"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->
	    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
			<span class="info-box-icon bg-red"><i class="ion ion-ios-calculator"></i></span>
			 

	        <div class="info-box-content">
	          <span class="info-box-text">Total Expenses</span>
	          <span class="info-box-number total_expenses"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->
	    <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
	        <span class="info-box-icon bg-green">	        	 
				<i class="fa fa-heart"></i>
	        </span>

	        <div class="info-box-content">
	          <span class="info-box-text">Total Profit</span>
	          <span class="info-box-number total_profit"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
			<!-- /.info-box-content -->
			 
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->

	    <!-- fix for small devices only -->
	    <!-- <div class="clearfix visible-sm-block"></div> -->
	    {{-- <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
	      <div class="info-box info-box-new-style">
	        <span class="info-box-icon bg-yellow">
	        	<i class="ion ion-ios-paper-outline"></i>
	        	<i class="fa fa-exclamation"></i>
	        </span>

	        <div class="info-box-content">
	          <span class="info-box-text">{{ __('home.invoice_due') }}</span>
	          <span class="info-box-number invoice_due"><i class="fas fa-sync fa-spin fa-fw margin-bottom"></i></span>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div> --}}
	    <!-- /.col -->
</div>

<div class="row mt-5">
	<div class="col-sm-12">
        @component('components.widget', ['class' => 'box-primary','title'=>'QUICK ACCESS'])        
			<div class="col-sm-12 col-md-3">
				<a href="/reports/cost-of-sales">
					<div class="small-box" style="background-color: #396fef;color: #fff;">
						<div class="inner text-center">
							  <h4 class="text-white">COST OF SALE</h4>
							  <p>Click to view Report</p>
						</div>
					  </div>
				</a>				
			</div>
			<div class="col-sm-12 col-md-3">
				<a href="/reports/stock-report">
					<div class="small-box" style="background-color: #396fef;color: #fff;">
						<div class="inner text-center">
							  <h4 class="text-white">STOCK ON HAND</h4>
							  <p>Click to view Report</p>
						</div>
					  </div>
				</a>				
			</div>
			<div class="col-sm-12 col-md-3">
				<a href="/accounting/report/pnl">
					<div class="small-box" style="background-color: #396fef;color: #fff;">
						<div class="inner text-center">
							  <h4 class="text-white">PROFI & LOSS</h4>
							  <p>Click to view Report</p>
						</div>
					  </div>
				</a>				
			</div>
			<div class="col-sm-12 col-md-3">
				<a href="/accounting/ledger">
					<div class="small-box" style="background-color: #396fef;color: #fff;">
						<div class="inner text-center">
							  <h4 class="text-white">LEDGERS</h4>
							  <p>Click to view Report</p>
						</div>
					  </div>
				</a>				
			</div>
			<div class="col-sm-12 col-md-3">
				<a href="#">
					<div class="small-box" style="background-color: #396fef;color: #fff;">
						<div class="inner text-center">
							  <h4 class="text-white">CASH IN HAND</h4>
							  <p>Click to view Report</p>
						</div>
					</div>
				</a>				
			</div>
			<div class="col-sm-12 col-md-3">
				<a href="/accounting/control-accounts/creditors">
					<div class="small-box" style="background-color: #396fef;color: #fff;">
						<div class="inner text-center">
							  <h4 class="text-white">CREDITORS</h4>
							  <p>Click to view Report</p>
						</div>
					  </div>
				</a>				
			</div>
			<div class="col-sm-12 col-md-3">
				<a href="/accounting/control-accounts/debtor">
					<div class="small-box" style="background-color: #396fef;color: #fff;">
						<div class="inner text-center">
							  <h4 class="text-white">DEBTORS</h4>
							  <p>Click to view Report</p>
						</div>
					  </div>
				</a>				
			</div>
			<div class="col-sm-12 col-md-3">
				<a href="/accounting/report/trial-balance">
					<div class="small-box" style="background-color: #396fef;color: #fff;">
						<div class="inner text-center">
							  <h4 class="text-white">TRIAL BALANCE</h4>
							  <p>Click to view Report</p>
						</div>
					  </div>
				</a>				
			</div>
        @endcomponent
	  </div>
</div>

  <div class="row">
	
	@if(count($bank_accounts)>0)
      <div class="col-sm-6">
        @component('components.widget', ['class' => 'box-primary','title'=>'BANK ACCOUNT BALANCES - ( CURRENT )'])        
        <table class="table table-striped">            
            <tbody>   
                @foreach ($bank_accounts as $account)
                   <tr>
                    <th>{{$account->account_name}} - ( {{$account->account_no}} ) </th>
                    <th class="text-right">{{number_format($account->balance,2,'.',',')}}</th>
                   </tr>
                @endforeach
            </tbody>
        </table>
        @endcomponent
	  </div>
	  @endif

	  <div class="col-sm-6">
        @component('components.widget', ['class' => 'box-primary','title'=>'DEFAULT IN HAND ACCOUNTS BALANCES - ( CURRENT )'])        
        <table class="table table-striped">            
            <tbody>   
                @foreach ($default_in_hand_accounts as $account)
                   <tr>
                    <th>{{$account->account_name}} - ( {{$account->account_no}} ) </th>
                    <th class="text-right">{{number_format($account->balance,2,'.',',')}}</th>
                   </tr>
                @endforeach
            </tbody>
        </table>
        @endcomponent
	  </div>
  </div>

  <div class="row">
	<div class="col-sm-12">
        @component('components.widget', ['class' => 'box-primary','title'=>'CASH FLOW'])        
        <table class="table table-striped">            
             
        </table>
        @endcomponent
	  </div>
  </div>

  <div class="row">
	<div class="col-sm-12">
        @component('components.widget', ['class' => 'box-primary','title'=>'RECENT TRANSACTIONS'])        
        <table class="table table-striped">            
             
        </table>
        @endcomponent
	  </div>
  </div>





</section>
<!-- /.content -->

@stop
@section('javascript')
    {{-- @include('manufacturing::layouts.partials.common_script') --}}
<script>
	$(document).ready(function() {

	var start = $('input[name="date-filter"]:checked').data('start');
    var end = $('input[name="date-filter"]:checked').data('end');
    update_statistics(start, end);
    $(document).on('change', 'input[name="date-filter"], #dashboard_location', function() {
        var start = $('input[name="date-filter"]:checked').data('start');
        var end = $('input[name="date-filter"]:checked').data('end');
        update_statistics(start, end);
    });


	});

	function update_statistics(start, end) {
     
    var data = { start_date: start, end_date: end};
    //get purchase details
    var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
    $('.total_income').html(loader);
    $('.total_expenses').html(loader);
    $('.total_profit').html(loader);
    
    $.ajax({
        method: 'POST',
        url: '/accounting/dashboard/getdata',
        dataType: 'json',
        data: data,
        success: function(data) {
            //income details
            $('.total_income').html(__currency_trans_from_en(data.toal_income, true));
            
			//Expenses details
            $('.total_expenses').html(__currency_trans_from_en(data.total_expenses, true));

            //Profit details
            $('.total_profit').html(__currency_trans_from_en(data.total_profit, true));
            
        },
    });
}
</script>
<style>
	.short-cut{
		background: #3595f6
	}
</style>
@endsection
