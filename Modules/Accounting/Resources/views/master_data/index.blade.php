@extends('layouts.app')
@section('title', 'Acconting')
 
@section('content')
@include('accounting::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Master Data</h1>
</section>

<!-- Main content -->
<section class="content">
     
    <div class="row">
        <div class="col-xs-12">
           <!--  <pos-tab-container> -->
            <div class="col-xs-12 pos-tab-container">
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu">
                    <div class="list-group">
                        <a href="#" class="list-group-item text-center active">Category</a>
                        {{-- <a href="#" class="list-group-item text-center active">Category</a> --}}
                        <a href="#" class="list-group-item text-center">@lang('messages.settings')</a>
                       
                    </div>
                   
                </div>
                <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                    
                    <div class="pos-tab-content active">
                        {!! Form::open(['url' => action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountCategoryController@store'), 'method' => 'post', 'id' => 'account_category_form', 'files' => true ]) !!}
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Account Type</label>
                                        {!! Form::select('account_type', $account_types, null, ['class' => 'form-control select2', 'required']); !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="">Category Code</label>
                                        {!! Form::text('category_code', '', ['placeholder' => 'Category Code', 'class' => 'form-control','required']); !!}
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="">Category Name</label>
                                        {!! Form::text('category_name', '',['placeholder' =>'Category name', 'class' => 'form-control','required']); !!}
                                    </div>
                                </div>                                                       
                            </div>
                            <div class="row">
                                <div class="col-md-12 float-right">
                                    <div class="form-group">                                     
                                        <button type="submit" class="btn btn-primary pull-right">Save</button>
                                    </div>
                                </div>
                            </div>

                        {!! Form::close() !!}

                        <div class="row mt-5">
                            <div class="table-responsive" style="padding-right: 15px">
                                <table class="table table-bordered table-striped" id="account_type_table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Type</th>
                                            <th>Code</th>
                                            <th>Category Name</th>                                            
                                            <th>Sort Order</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>                                    
                                </table>
                            </div>
                        </div>                        
                    </div>
                    
                    <div class="pos-tab-content">
                        {{-- <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('ref_no_prefix', __('manufacturing::lang.mfg_ref_no_prefix') . ':' ) !!}
                                    {!! Form::text('ref_no_prefix', !empty($manufacturing_settings['ref_no_prefix']) ? $manufacturing_settings['ref_no_prefix'] : null, ['placeholder' => __('manufacturing::lang.mfg_ref_no_prefix'), 'class' => 'form-control']); !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <br>
                                    <div class="checkbox">
                                        <label>
                                        {!! Form::checkbox('disable_editing_ingredient_qty', 1, !empty($manufacturing_settings['disable_editing_ingredient_qty']), ['class' => 'input-icheck', 'id' => 'disable_editing_ingredient_qty']); !!} @lang('manufacturing::lang.disable_editing_ingredient_qty')
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <br>
                                    <div class="checkbox">
                                        <label>
                                        {!! Form::checkbox('enable_updating_product_price', 1, !empty($manufacturing_settings['enable_updating_product_price']), ['class' => 'input-icheck', 'id' => 'enable_updating_product_price']); !!} @lang('manufacturing::lang.enable_editing_product_price_after_production')
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        
                    </div>
                   
                </div>
            </div>
            <!--  </pos-tab-container> -->
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary pull-right">@lang('messages.update')</button>
        </div>
    </div> --}}

    <div class="col-xs-12">
        {{-- <p class="help-block"><i>{!! __('manufacturing::lang.version_info', ['version' => $version]) !!}</i></p> --}}
    </div>
    
</section>


<!-- /.content -->
<div class="modal fade" id="recipe_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@stop
@section('javascript')
    @include('accounting::layouts.partials.common_script')
@endsection
