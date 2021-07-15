@extends('layouts.app')
@section('title', 'Acconting')
 
@section('content')
@include('accounting::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Accounts</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-solid','title'=>'Create New Account'])
    {!! Form::open(['url' => action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountController@store'), 'method' => 'post', 'id' => 'account_form', 'files' => true ]) !!}
    
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="">Account Type <span class="text-danger">*</span></label>
                {!! Form::select('account_type', $account_types, null, ['id' => 'account_type' ,'class' => 'form-control select2', 'required']); !!}
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group">
                <label for="">Account Category <span class="text-danger">*</span></label>
                {!! Form::select('account_category', $account_categories, null, ['id' => 'account_category','class' => 'form-control select2', 'required']); !!}
            </div>
        </div>
    </div>
    <div class="row"> 
        <div class="col-md-8">
            <div class="form-group">
                <label for="">Account Name <span class="text-danger">*</span></label>
                {!! Form::text('account_name', '',['placeholder' =>'Name', 'class' => 'form-control','required']); !!}
            </div>
        </div>        
    </div>
    <div class="row">  
        <div class="col-md-4">
            <div class="form-group">
                <label for="">Account Number <span class="text-danger">*</span></label>
                {!! Form::text('account_no', '', ['placeholder' => 'A/C Number', 'class' => 'form-control','required']); !!}
            </div>
        </div>
        <div id="is_bank_div" class="col-md-2">
           
        </div>
    </div>
    <div class="row ">
        <div class="col-md-12 float-right">
            <div class="form-group">                                     
                <button type="submit" class="btn btn-primary pull-right">Save</button>
            </div>
        </div>
    </div>

{!! Form::close() !!}
    @endcomponent
</section>
<!-- /.content -->


@stop
@section('javascript')
    @include('accounting::layouts.partials.common_script')
    <script>
        $(document).on('change', '#account_category', function() {
			var category_id = $('#account_category').val();
           var df_ct_id = '{{$current_asset_category_id}}';
            
			if(category_id == parseInt(df_ct_id)){                
                 $("#is_bank_div").html(
                    `<label for="sada"></label>
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('is_bank', 1, '', 
                            [ 'class' => 'input-icheck']); !!} Is Bank Account
                        </label>
                    </div>`
                 );
                
			}else{
                $("#is_bank_div").html(null);
            }
		});
    </script>
@endsection
