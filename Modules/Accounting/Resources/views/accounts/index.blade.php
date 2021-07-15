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
    @component('components.widget', ['class' => 'box-solid','title'=>'Account List'])
       
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountController@create')}}">
                    <i class="fa fa-plus"></i>Add New</a>
            </div>
        @endslot
       
        <div class="table-responsive">
            <table class="table table-bordered" id="account_table">
                <thead>
                    <tr>
                        <th width="5%">Action</th>
                        <th width="10%">Account Code</th>
                        <th width="10%">Account No</th>
                        <th width="35%">Account Name</th>
                        <th width="5%">Account Type</th>
                        <th width="20%">Category</th>
                        <th width="10%">Balance</th>
                        <th width="5%">Status</th>
                    </tr>
                </thead>                
            </table>
        </div>
    @endcomponent
</section>
<!-- /.content -->

@stop
@section('javascript')
    @include('accounting::layouts.partials.common_script')
@endsection
