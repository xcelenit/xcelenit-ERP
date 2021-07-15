@extends('accounting::layouts.doc')
 
@section('content')

<table class="head-table">
    <tbody>
        <tr>
            <th colspan="2"><span class="heading" >PAYMENT VOUCHER</span></th>            
        </tr>
        <tr>
            <th style="text-align: left;"><span class="heading" >{{$busines_name}}</span> </th>
            <td style="text-align: right;"><b>Date/Time: - {{date('Y-m-d h:i:A')}}</b></td>
        </tr>
    </tbody>
</table> 
<hr class="line">    
<table class="detail-table">
    <tbody>
        <tr>
            <th>VOU NO </th>
            <th class="colan">:</th>
            <td>{{$transaction->document_no}}</td>
            <th>BANK </th>
            <th>:</th>
            <td>{{$transaction->getCreditAccountAttribute()}}</td>
        </tr>
        <tr>
            <th>DOC DATE  </th>
            <th>:</th>
            <td>{{strtoupper(date('d-M-Y', strtotime($transaction->transaction_date)))}}</td>
            <th>CHQ DATE </th>
            <th>:</th>
            <td>{{strtoupper(date('d-M-Y', strtotime($transaction->cheque_date)))}}</td>
        </tr>
        <tr>
            <th>CHQ Number </th>
            <th>:</th>
            <td>{{$transaction->cheque_no}}</td>
            <th>SUP Name  </th>
            <th>:</th>
            <td>{{ isset($transaction->vendor) ? ($transaction->vendor->supplier_business_name!=null ? $transaction->vendor->supplier_business_name : $transaction->vendor->name) : ''}} </td>
        </tr>
        <tr>
            <th>PAYEE</th>
            <th>:</th>
            <td colspan="3">{{$transaction->payee}}</td>             
        </tr>
    </tbody>
</table> 
<table class="bordered-table">
    <tbody>
        <tr>          
            <th colspan="2"  style="text-align: left;" width="40%">DESCRIPTION</th>
            <th width="20%">LKR AMOUNT</th>
        </tr>
        <tr >           
            <td colspan="2" style="height: 80px; vertical-align: top;">
                {{$transaction->payment_note}}
            </td>
            <td style="text-align: right; vertical-align: top;">
                {{number_format($transaction->total_amount,2,'.',',')}}                
            </td>
        </tr>
        <tr>
            <th style="min-height: 25px; text-align: right" colspan="2">TOTAL :</th>
            <th style="min-height: 25px; text-align: right">{{number_format($transaction->total_amount,2,'.',',')}}  </th>
        </tr>
        <tr>
            <td style="min-height: 25px; text-align: left; padding:8px;" colspan="3"><b>TOTAL LKR :</b> {{isset($total_in_word) ? ucfirst($total_in_word).' only' : null}}  </td>
        </tr>
    </tbody>
</table>
<table class="detail-table">
    <tbody > 
        <tr>
            <th width="15%">INVOICES(S) : </th>
            <th width="85%" style="text-align: left;">    
                                 
                @if($transaction->transactionDetails->count()>0)
                    @foreach($transaction->transactionDetails as $reff)
                        <span>{{$reff->ref_no}}</span>
                    @endforeach
                @endif
            </th>
        </tr>
    </tbody>
</table>

<table class="footer-table">
    <tbody> 
        <tr>
            <td width="25%">-----------------</td>
            <td width="25%">-----------------</td>
            <td width="25%">-----------------</td>
            <td width="25%">-----------------</td>
        </tr>  
        <tr>
            <td width="25%">Customer</td>
            <td width="25%">Prepaired By</td>
            <td width="25%">Accountant</td>
            <td width="25%">Chairman</td>             
        </tr>      
    </tbody>
</table>

<style>
    .head-table{
        width: 100%; 
        padding:0px;
        margin: 8px;
        text-align: left;
         
    }
    .detail-table{
        width: 100%; 
        padding:0px;
        margin: 8px;
        text-align: left;
    }
    .footer-table{
        width: 100%; 
        padding:0px;
        margin: 50px 5px 5px 5px;
        text-align: center;
    }
    .bordered-table{
        width: 100%; 
        padding:0px;
        margin: 8px;
        text-align: left;   
        border-collapse: collapse; 
        
    }
    .bordered-table th, .bordered-table td {
        border: 1px solid black;
        padding:5px 5px 5px 5px;
    }
    .detail-table th{
        text-align: left;
        font-size: 16px;
        /* width: 120px; */
    }
    .detail-table tr th {
        margin-top: 0px;
        margin-bottom: 0px;
    }
    /* .detail-table th .colan{
        width: 5px;
        text-align: left;
        font-size: 16px;
    } */
     
    h2 {
        margin: 0px;
        
    }
    .line {
        /* top  right  bottom left */
        margin: 10px 8px 8px 8px;
        height: 1px;
        background-color: rgb(145, 145, 145);
    }
    .heading {
        font-size: 21.33px;
    }
</style>

@stop

