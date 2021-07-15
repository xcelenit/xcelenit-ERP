 
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('document_type',  'Document' . ':') !!}

        {!! Form::select('document_type', $document_types, null, ['id'=>'document_type','class' => 'form-control select2', 'style' => 'width:100%' ]); !!}
    </div>
</div> 

<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('vendor_id', 'Vendor' . ':') !!}
        {!! Form::select('vendor_id', $vendors, null, ['class' => 'form-control select2', 'id'=>'vendor_id', 'style' => 'width:100%']); !!}
    </div>
</div>

 
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('payment_status',  __('purchase.payment_status') . ':') !!}
        {!! Form::select('payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'patial' => __('lang_v1.partial'), 'unpaid' => 'Unpaid'], null, ['id'=>'payment_status','class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
    </div>
</div>


<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
        {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
    </div>
</div>


<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('added_by',  'Added By') !!}
        {!! Form::select('added_by', $users, null, ['id'=>'added_by','class' => 'form-control select2', 'style' => 'width:100%']); !!}
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('location',  'Location') !!}
        {!! Form::select('location_id', $business_locations, null, ['id'=>'location_id','class' => 'form-control select2', 'style' => 'width:100%']); !!}
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('disc',  'INV/GRN/RFF No') !!}
        {!! Form::text('ref_no',null, ['id'=>'ref_no','class' => 'form-control', 'style' => 'width:100%']); !!}
    </div>
</div>

{{-- 
@if(empty($only) || in_array('only_subscriptions', $only))
<div class="col-md-3">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <br>
              {!! Form::checkbox('only_subscriptions', 1, false, 
              [ 'class' => 'input-icheck', 'id' => 'only_subscriptions']); !!} {{ __('lang_v1.subscriptions') }}
            </label>
        </div>
    </div>
</div>
@endif --}}