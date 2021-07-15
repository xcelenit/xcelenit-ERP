<?php

namespace Modules\Accounting\Entities\Utility;

use App\User; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryTransaction;
use Modules\Accounting\Entities\DoubleEntryLedgerTransaction;

class AccountUtility extends Model
{
     //Get Account Transaction
     public function getControlAccountTransaction($business_id,$type, $contact_id, $start_date, $end_date)
     {
         
         if($type == 'debtor'){
            $account_id = config('accounting.default_account_ids.debtor');
        }else{
            $account_id = config('accounting.default_account_ids.creditor'); 
        }
        
         return  $this->getLedgerTransaction($business_id,$account_id, $contact_id, $start_date, $end_date);
                 
     }

     public function getLedgerAccountTransaction($business_id, $account_id, $start_date, $end_date)
     {
         
         return  $this->getLedgerTransaction($business_id, $account_id, $contact_id, $start_date, $end_date);
                 
     }



     public function getLedgerTransaction($business_id, $account_id , $contact_id, $start_date, $end_date)
     {  
                  $account = DoubleEntryAccount::find($account_id);
                  $openning_bl=0;

                  if(isset($account)){
                        $openning_bl = $account->getBalance($contact_id, $start_date);
                  }

                 // \Log::emergency($openning_bl);
        
                $ledger_transactions = DoubleEntryLedgerTransaction::join('acc_accounts AS A','A.id','=','acc_ledger_transactions.account_id')
                    ->join('acc_transactions as AT','AT.id','=','acc_ledger_transactions.transaction_id')
                    ->join('users as US','US.id','=','AT.added_by')
                    ->with(['transactionDetails','transactionPaymentDetails.transaction'])
                    ->where('A.id',$account_id)
                    ->where('AT.business_id',$business_id)
                    ->whereBetween(DB::raw('date(AT.transaction_date)'), [$start_date, $end_date])
                    ->where('AT.is_canceled',0);

                    if(isset($contact_id)){
                        $ledger_transactions->where('AT.vendor_id',$contact_id);
                    }          
                    
                    // $ledger_transactions->orderBy(DB::raw('date(AT.transaction_date)'), 'ASC');

                    $ledger_transactions->select(['acc_ledger_transactions.*','AT.added_by','AT.vendor_id','AT.document_no','AT.document_type','AT.transaction_date','AT.payment_note','AT.payment_method',
                                                  'A.account_type_id as ac_type','AT.is_opening_bl','US.username as added_user','AT.cheque_no','AT.cheque_date',
                            
                        // DB::raw("(SELECT @b := (IF(acc_ledger_transactions.entry_type=IF((ac_type <= 2), 'DR', 'CR'), 
                        //                  @b + acc_ledger_transactions.amount, 
                        //                  @b - acc_ledger_transactions.amount)) as balance FROM (SELECT @b := $openning_bl) as dummy ) balance")

                        DB::raw("(SUM(
                            IF(acc_ledger_transactions.entry_type=IF((A.account_type_id <= 2), 'DR', 'CR'), acc_ledger_transactions.amount, -1 * acc_ledger_transactions.amount)
                        ) 
                        OVER (ORDER BY AT.transaction_date ASC, acc_ledger_transactions.id ASC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) + $openning_bl) as balance")
                                    
                                        //  DB::raw("(SELECT @b := (IF(acc_ledger_transactions.entry_type=IF((ac_type <= 2), 'DR', 'CR'), 
                                        //  @b + $openning_bl + acc_ledger_transactions.amount, 
                                        //  @b + $openning_bl - acc_ledger_transactions.amount)) as balance FROM (SELECT @b := $openning_bl) as dummy ) balance")                            
                                        // DB::raw('(SELECT SUM(IF(ALE.entry_type=IF((ac_type <= 2), "DR", "CR"), ALE.amount, -1 * ALE.amount)) FROM acc_ledger_transactions AS ALE 
                                        //         INNER JOIN acc_transactions AS ATS ON ALE.transaction_id = ATS.id WHERE ALE.account_id = acc_ledger_transactions.account_id AND date(ATS.transaction_date) <= date(AT.transaction_date)
                                        //         AND ATS.is_canceled=0 AND ALE.id <= acc_ledger_transactions.id) as balance')
                         ])             // IF(A.account_type_id >= 3,"CR","DR")
                    // ->groupBy('acc_ledger_transactions.id')
                    ->orderBy('AT.transaction_date','ASC')
                    ->orderBy('acc_ledger_transactions.id','ASC');
                    
                 
        return $ledger_transactions;
     }

     public function getAccountSummary($account_id, $contact_id=null)
     {
         $account = DoubleEntryAccount::find($account_id);  

         return $account->getSummary($contact_id);
     }

     public function mapDocumentType($document_type, $transaction, $transactionDetails)
     {
         $document_type_array = collect(config('accounting.document_type'));         
         $filtered = $document_type_array->where('code', $document_type)->first();
         $str='';
         if(isset($filtered)){

           

            if($transaction->payment_method!=null){

               // $str.=strtoupper($transaction->payment_method).' '.$filtered['type'];
                if($filtered['code']=='RCP'){
                    $str.=strtoupper($transaction->payment_method).' '.$filtered['type'];
                }else{
                    $str.=$filtered['type'];
                }
               
                if($transaction->is_opening_bl==1){
                    $str.='<span class="text-green"> - OPENING BALANCE </span>'; 
                }
                
                if($transaction->cheque_no!=null){
                    $str.=' ( Cheque No : '.$transaction->cheque_no;
                    if($transaction->payee!=null){
                        $str.=' | Payee : '.$transaction->payee;
                    }
                    $str.=' )';
                }

                               
            }else{
                $str=$filtered['type'].' : ';
                if($transaction->is_opening_bl==1){
                    $str.='<span class="text-green"> - OPENING BALANCE </span>'; 
                }
            }

            $count=0;
            foreach($transactionDetails as $transDetail){
                $count++;
                $str .='<span class="text-info"> <a href="#">'.$transDetail->ref_no.'</a></span>';
                if(count($transactionDetails) > $count){
                    $str .=',';
                }
            }

            if($transaction->payment_note!=null){
                $str.=' | Payment Note: <small>'.$transaction->payment_note.'</small> :';
            } 
            
            $str.=' | <span class="text-grey"> Added By : <i>'.$transaction->added_user.'</i></span>'; 
            return $str;

         }else{
                                 

            return null;
         }
         
        
     }

    

     public function getDocumentTypeByCode($code)
     {
        $document_type_array = collect(config('accounting.document_type'));         
        $filtered = $document_type_array->where('code', $code)->first();

        return $filtered;
     } 

     

     public function getUnpaidDebtorInvoice($business_id, $vendor_id)
     {  
         

         $sales_invoices = DoubleEntryTransaction::with(['transactionDetails'])
                                                 ->with(['location'=> function($queary){
                                                    $queary->select('id','name');
                                                 }])
                                                 ->where('acc_transactions.vendor_id',$vendor_id)
                                                 ->whereIn('acc_transactions.document_type', [config('accounting.document_type_prefix.sales'),config('accounting.document_type_prefix.journal_vo')]) 
                                                 ->where('acc_transactions.is_canceled', 0)
                                                 ->whereIn('acc_transactions.payment_status', ['due','patial'])
                                                //  ->whereNotIn('acc_transactions.payment_status', ['paid'])                                                                                             
                                                 ->select('acc_transactions.id','acc_transactions.location_id','acc_transactions.document_no','acc_transactions.transaction_date','acc_transactions.payment_status','acc_transactions.total_amount',
                                                    
                                                 DB::raw('(SELECT SUM(ATD.sub_amount) FROM acc_transactions AS RC 
                                                             INNER JOIN acc_transaction_details AS ATD 
                                                             ON RC.id = ATD.transaction_id 
                                                             WHERE RC.document_type IN ("RCP","JNV","BDA","CDN") AND RC.is_canceled="0" AND ATD.perent_transaction_id = acc_transactions.id ) AS total_paid')
                                                 )->get();
                                           
        
        return $sales_invoices;
     }

     public function getUnpaidDebtorInvoiceWithTransactionData($business_id, $vendor_id, $transaction_id)
     {  
         
         
         $sales_invoices = DoubleEntryTransaction::leftJoin('acc_transaction_details as acc_td','acc_td.perent_transaction_id','=','acc_transactions.id')                                                 
                                                 ->with(['transactionDetails'])
                                                 ->with(['location'=> function($queary){
                                                    $queary->select('id','name');
                                                 }])
                                                 ->where('acc_transactions.vendor_id',$vendor_id)
                                                 ->where('acc_transactions.document_type', config('accounting.document_type_prefix.sales'))                                                 
                                                 ->where('acc_transactions.is_canceled', 0)
                                                 
                                                 ->whereRaw('IF(acc_transactions.payment_status="paid", acc_td.transaction_id = '.$transaction_id.', acc_transactions.payment_status !="paid")')
                                                                                 
                                                 ->select('acc_transactions.id','acc_transactions.location_id','acc_transactions.document_no','acc_transactions.transaction_date','acc_transactions.payment_status','acc_transactions.total_amount',
                                                    'acc_td.sub_amount','acc_td.transaction_id AS trans_id',
                                                 DB::raw('(SELECT SUM(ATD.sub_amount) FROM acc_transactions AS RC 
                                                             INNER JOIN acc_transaction_details AS ATD 
                                                             ON RC.id = ATD.transaction_id 
                                                             WHERE RC.document_type IN ("RCP","JNV","BDA") AND RC.is_canceled="0" AND ATD.perent_transaction_id = acc_transactions.id AND ATD.transaction_id !="'.$transaction_id.'" ) AS total_paid')
                                                 )->get();
                                          
        
        return $sales_invoices;
     }

     
     public function getUnpaidCreditorInvoice($business_id, $vendor_id)
     {  
         
         $sales_invoices = DoubleEntryTransaction::with(['transactionDetails'])
                                                 ->with(['location'=> function($queary){
                                                    $queary->select('id','name');
                                                 }])
                                                 ->where('acc_transactions.vendor_id',$vendor_id)
                                                 ->whereIn('acc_transactions.document_type',[
                                                     config('accounting.document_type_prefix.grn'),
                                                     config('accounting.document_type_prefix.expenses'),
                                                     config('accounting.document_type_prefix.journal_vo')])                                                 
                                                 ->where('acc_transactions.is_canceled', 0)  
                                                 ->whereIn('acc_transactions.payment_status', ['due','patial'])                                               
                                                 ->select('acc_transactions.id','acc_transactions.location_id','acc_transactions.document_no','acc_transactions.transaction_date','acc_transactions.payment_status','acc_transactions.total_amount',
                                                    
                                                 DB::raw('(SELECT SUM(ATD.sub_amount) FROM acc_transactions AS VC
                                                             INNER JOIN acc_transaction_details AS ATD 
                                                             ON VC.id = ATD.transaction_id 
                                                             WHERE VC.document_type IN ("PCV","CPV","BCA","JNV") AND VC.is_canceled="0" AND ATD.perent_transaction_id = acc_transactions.id ) AS total_paid')
                                                 )
                                                 ->get();

            return $sales_invoices;
     }

     public function getUnpaidCreditorInvoiceWithTransactionData($business_id, $vendor_id,$transaction_id)
     {  
         
         $sales_invoices = DoubleEntryTransaction::leftJoin('acc_transaction_details as acc_td','acc_td.perent_transaction_id','=','acc_transactions.id')
                                                 ->with(['transactionDetails'])
                                                 ->with(['location'=> function($queary){
                                                    $queary->select('id','name');
                                                 }])
                                                 ->where('acc_transactions.vendor_id',$vendor_id)
                                                 ->whereIn('acc_transactions.document_type',[
                                                     config('accounting.document_type_prefix.grn'),
                                                     config('accounting.document_type_prefix.expenses'),
                                                     config('accounting.document_type_prefix.journal_vo')])                                                 
                                                 ->where('acc_transactions.is_canceled', 0)  
                                                //  ->whereIn('acc_transactions.payment_status', ['due','patial'])                                               

                                                 ->whereRaw('IF(acc_transactions.payment_status="paid", acc_td.transaction_id = '.$transaction_id.', acc_transactions.payment_status !="paid")')
                                                 ->select('acc_transactions.id','acc_transactions.location_id','acc_transactions.document_no','acc_transactions.transaction_date','acc_transactions.payment_status','acc_transactions.total_amount',
                                                 'acc_td.sub_amount','acc_td.transaction_id AS trans_id',
                                                    
                                                 DB::raw('(SELECT SUM(ATD.sub_amount) FROM acc_transactions AS VC
                                                             INNER JOIN acc_transaction_details AS ATD 
                                                             ON VC.id = ATD.transaction_id 
                                                             WHERE VC.document_type IN ("PCV","CPV","BCA","JNV") AND VC.is_canceled="0" AND ATD.perent_transaction_id = acc_transactions.id AND ATD.transaction_id !="'.$transaction_id.'") AS total_paid')
                                                 )
                                                 ->get();

            return $sales_invoices;
     }



     
}

/*
leager account name      debit   credit  
asset
    non current asset
        propert
    