@extends('accounting::layouts.doc')
 
@section('content')
<span class="left-date">{{date('d/m/Y',strtotime($transaction->cheque_date))}}</span>
<span class="left-payee">{{$transaction->payee}}</span>
<span class="left-desc">{{$transaction->payment_note}}</span>
<span class="left-amount">{{number_format($transaction->total_amount,2,'.',',')}}</span>
<span class="left-vc-no">{{$transaction->document_no}}</span>
<span class="chq-acpo">A/C PAYEE ONLY</span>
<span class="chq-payee">{{$transaction->payee}}</span>


<span class="chq-amount-wd">{{isset($total_in_word) ? ucfirst($total_in_word) : null}} Only</span>
<span class="chq-amount">**{{number_format($transaction->total_amount,2,'.',',')}}</span>

<span class="chq-date">{{date('dmY',strtotime($transaction->cheque_date))}}</span>
@stop
 
<style>

    .left-date {
        font-family: monospace;
        font-size: 12px;
        position: absolute;
        left: 57.7mm;
        top: 8mm;
    }

    .left-payee {
        font-family: monospace;
        width: 38mm;
        font-size: 12px;
        position: absolute;
        left: 50mm;
        top: 13mm;
    }
    .left-desc {
        font-family: monospace;
        width: 45mm;
        font-size: 11px;        
        position: absolute;
        left: 42.6mm;
        top: 23.3mm;
    }
    .left-amount {
        font-family: monospace;
        width: 140px;
        font-size: 12px;
        text-align: right;
        position: absolute;
        left: 46.8mm;
        top: 54.2mm;
    }
    .left-vc-no {
        font-family: monospace;
        width: 140px;
        font-size: 12px;
        text-align: right;
        position: absolute;
        left: 47.3mm;
        top: 70.5mm;
    }
    
</style>

<style>
    .chq-acpo{
        font-family: monospace;
        width: 60mm;
        font-size: 16px;
        text-align: left;
        position: absolute;
        left: 125.0mm;
        top: 12mm;
    }
    .chq-payee{
        font-family: monospace;
        width: 160mm;
        font-size: 16px;
        text-align: left;
        position: absolute;
        left: 103.0mm;
        top: 19mm;
        /* border: 1px solid black; */
    }
    .chq-amount-wd {
        font-family: monospace;
        width: 88mm;
        height: 20mm;
        font-size: 16px;
        text-align: left;
        position: absolute;
        left: 105.2mm;
        top: 28.0mm;
        border: 1px dotted black;
        line-height: 8mm;
    }
    .chq-date{
        font-family: monospace;
        width: 54mm;
        letter-spacing: 3.5mm;
        font-weight: bold;
        font-size: 16px;        
        text-align: left;
        position: absolute;
        left: 201.3mm;
        top: 9mm;
        /* border: 1px dotted black; */
    }
    .chq-amount {
        font-family: monospace;
        width: 48mm;        
        /* height: 10mm; */
        font-size: 16px;
        text-align: center;
        position: absolute;
        left: 198.3mm;
        top: 37.1mm;
        border: 1px dotted black;
        
    }
   
</style>