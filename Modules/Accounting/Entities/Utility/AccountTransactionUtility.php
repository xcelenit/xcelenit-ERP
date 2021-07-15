<?php

namespace Modules\Accounting\Entities\Utility;

use App\Business;
use App\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryTransaction;
use Modules\Accounting\Entities\DoubleEntryTransactionDetail;
use Modules\Accounting\Entities\DoubleEntryTransactionSchemes;

class AccountTransactionUtility extends Model
{
    

    public function createSalesEntry($business_id, $transaction, $payments)
    {   
        //

        $reference_line=[];
        $reference_line['ref_no'] = $transaction['invoice_no'];
        $reference_line['desc'] = null;
        $reference_line['perent_transaction_id'] = null;
        $reference_line['sub_amount'] = null;
        $transaction_id = null;
        if(isset($transaction['transaction_id'])){
            $transaction_id = $transaction['transaction_id'];
        }
        
        $reference_lines[]= $reference_line;

       // return $reference_lines;

        $debit_account_id = config('accounting.default_account_ids.debtor');
        $credit_account_id = config('accounting.default_account_ids.sales');

       $Sale_transaction =  $this->CreateTransaction($business_id,$transaction_id , 
                                        $transaction['location_id'],$transaction['contact_id'],$reference_lines, 'SAL', null, 
                                        $transaction['transaction_date'],$transaction['total_amount'],
                                        null,null,null,null,null, $debit_account_id, $credit_account_id, 1, 'due',null, $transaction['sys_transaction_id']);
        

       if($Sale_transaction!='error'){
            $receipt_transaction='';
            $costOfSale_transaction='';
            if((isset($transaction['total_cost']) && ($transaction['total_cost']) > 0 )){
                 
                $costOfSale_transaction = $this->updateCosOfSale($business_id, $transaction['location_id'],$transaction,$reference_lines,$Sale_transaction->id, $Sale_transaction->document_no);
                
            }            

            if(isset($payments)){
                foreach($payments as $payment){

                   $reference_lines= [];

                   $reference_line['perent_transaction_id'] = $Sale_transaction->id;
                   $reference_line['sub_amount'] = $payment['total_amount'];
                   $reference_lines[] = $reference_line;

                   $receipt_transaction = $this->createReceipt($business_id,$transaction['location_id'],$transaction['contact_id'],$payment, $reference_lines, $Sale_transaction->id);
                   if($receipt_transaction=='error'){
                       break;
                   }
                }
            }
            

            if($receipt_transaction!='error' && $receipt_transaction!='error'){
                return 'done';
            }else{
                $this->removeTransaction($Sale_transaction->id);
                return 'error';
            }

        
            
        }else{
            return 'error';
        }
        

    }
        
    public function createReceipt($business_id,$location_id,$contact_id, $payment, $reference_lines, $perent_transaction_id=null)
    {   
                
        
        $debit_account_id = null;

        $transaction_details =[];
        
        $credit_account_id = config('accounting.default_account_ids.debtor');
        
        if(isset($payment['debit_account_id'])) {
            $debit_account_id = $payment['debit_account_id'];
        }else
        {   
            if($payment['payment_method']=='cash'){
                $debit_account_id = config('accounting.default_account_ids.cash_in_hand');                
            }else if($payment['payment_method']=='cheque'){
                $debit_account_id = config('accounting.default_account_ids.cheque_in_hand');                
            }else if($payment['payment_method']=='card'){
                $debit_account_id = config('accounting.default_account_ids.card_payment');                
            }else if($payment['payment_method']=='other'){
                $debit_account_id = config('accounting.default_account_ids.other_payment');                
            }else{
                return null;
            }
        }

        if(isset($payment['transaction_id'])){
            $transactions = DoubleEntryTransaction::find($payment['transaction_id']);
            
            $transaction_details = $transactions->transactionDetails()->get();
            
        }

                
        $Account_transaction =  $this->CreateTransaction($business_id, isset($payment['transaction_id']) ? $payment['transaction_id'] : null,
                                $location_id, $contact_id, $reference_lines, 'RCP',null, $payment['transaction_date'],$payment['total_amount'],
                                isset($payment['payment_method']) ? $payment['payment_method'] : null,
                                isset($payment['cheque_no']) ? $payment['cheque_no'] : null,
                                isset($payment['payee']) ? $payment['payee'] : null,
                                isset($payment['cheque_date']) ? $payment['cheque_date'] : null,
                                isset($payment['payment_note']) ? $payment['payment_note'] : null,
                                $debit_account_id, $credit_account_id, 1, null , $perent_transaction_id, isset($payment['sys_transaction_id']) ? $payment['sys_transaction_id']  : null);
        
        //Udpate Unadjusted
        if($Account_transaction!='error'){
            $this->updateUnAdjustedAmount($Account_transaction->id);
        }        
         
        //Update Older 
        foreach($transaction_details as $reference_line){
            $this->updateDebtorPaymentStatus($reference_line->perent_transaction_id);
        }

        //Update New 
        foreach($reference_lines as $reference_line ){
            $this->updateDebtorPaymentStatus($reference_line['perent_transaction_id']);
        }


        

        return  $Account_transaction;
    }

    public function createPurchaseEntry($business_id, $transaction)
    {
        $reference_line=[];
        $reference_line['ref_no'] = $transaction['purchase_ref'];
        $reference_line['desc'] = isset($transaction['supplier_invoice_no']) ? $transaction['supplier_invoice_no'] : null;
        $reference_line['perent_transaction_id'] = null;
        $reference_line['sub_amount'] = null;
        $reference_lines[]= $reference_line;

        $debit_account_id = config('accounting.default_account_ids.inventory');
        $credit_account_id = config('accounting.default_account_ids.creditor');
        
        $Purchase_transaction =  $this->CreateTransaction($business_id, isset($transaction['transaction_id']) ? $transaction['transaction_id'] : null,
                                        $transaction['location_id'],$transaction['contact_id'],$reference_lines, 'GRN', null,
                                        $transaction['transaction_date'],$transaction['total_amount'],
                                        null,null,null,null,isset($transaction['grn_note']) ? $transaction['grn_note'] : null, $debit_account_id, $credit_account_id, 1, 'due',null, $transaction['sys_transaction_id']);
        
        return $Purchase_transaction ;
    }

    public function createExpenseEntry($business_id, $transaction)
    {
        $reference_line=[];
        $reference_line['ref_no'] = $transaction['expenses_ref'];
        $reference_line['desc'] = isset($transaction['supplier_invoice_no']) ? $transaction['supplier_invoice_no'] : null;
        $reference_line['perent_transaction_id'] = null;
        $reference_line['sub_amount'] = null;
        $reference_lines[]= $reference_line;

        $debit_account_id = $this->mapExpenseAccountByCategory($transaction['category_id']);
        $credit_account_id = config('accounting.default_account_ids.creditor');
        
        $Expense_transaction =  $this->CreateTransaction($business_id, isset($transaction['transaction_id']) ? $transaction['transaction_id'] : null,
                                        $transaction['location_id'],$transaction['contact_id'],$reference_lines, 'EXP', null,
                                        $transaction['transaction_date'],$transaction['total_amount'],
                                        null,null,null,null,$transaction['expense_note'], $debit_account_id, $credit_account_id, 1, 'due',null, $transaction['sys_transaction_id']);
        
                                             
        return $Expense_transaction;
    }


    public function createCreditorVoucher($business_id,$location_id,$contact_id,$payment, $reference_lines, $perent_transaction_id=null)
    {
        $cheque_no = null;
        $credit_account_id = null;

        $debit_account_id = (isset($payment['debit_account_id']) ? $payment['debit_account_id'] :config('accounting.default_account_ids.creditor'));

        if(isset($payment['credit_account_id'])) {
            $credit_account_id = $payment['credit_account_id'];
            
            $Account_transaction =  $this->CreateTransaction($business_id, isset($payment['transaction_id']) ? $payment['transaction_id'] : null,
                                $location_id, $contact_id, $reference_lines, $payment['document_type'],null, $payment['transaction_date'],$payment['total_amount'],
                                isset($payment['payment_method']) ? $payment['payment_method'] : null,
                                isset($payment['cheque_no']) ? $payment['cheque_no'] : null,
                                isset($payment['payee']) ? $payment['payee'] : null,
                                isset($payment['cheque_date']) ? $payment['cheque_date'] : null,
                                isset($payment['payment_note']) ? $payment['payment_note'] : null,
                                $debit_account_id, $credit_account_id, 1, null , $perent_transaction_id);

          //Udpate Unadjusted
        if($Account_transaction!='error'){
            $this->updateUnAdjustedAmount($Account_transaction->id);
        }       
        
        foreach($reference_lines as $reference_line ){
            $this->updateCreditorPaymentStatus($reference_line['perent_transaction_id']);
        }                     

         return  $Account_transaction;
        }
               

    }

    public function createJornalVoucher($business_id,$location_id,$contact_id, $payment, $reference_lines, $is_opening_bl, $perent_transaction_id=null)
    {
        $cheque_no = null;
        $credit_account_id = null;
        $debit_account_id = null;

        $df_debtor_account_id = config('accounting.default_account_ids.debtor');
        $df_creditor_account_id = config('accounting.default_account_ids.creditor');


        //$debit_account_id = config('accounting.default_account_ids.creditor');

        // if(isset($payment['credit_account_id'])) {
            $credit_account_id = $payment['credit_account_id'];
            $debit_account_id = $payment['debit_account_id'];
            
            $payment_status =null;
            if($is_opening_bl==1){
                if(($debit_account_id==$df_debtor_account_id) || ($credit_account_id==$df_creditor_account_id)){
                   if($contact_id!=null){
                     $payment_status ='due';
                   }
                }
            }
           

            $Account_transaction =  $this->CreateTransaction($business_id, null,
                                $location_id, $contact_id, $reference_lines, $payment['document_type'],null, $payment['transaction_date'],$payment['total_amount'],
                                isset($payment['payment_method']) ? $payment['payment_method'] : null,
                                isset($payment['cheque_no']) ? $payment['cheque_no'] : null,
                                isset($payment['payee']) ? $payment['payee'] : null,
                                isset($payment['cheque_date']) ? $payment['cheque_date'] : null,
                                isset($payment['payment_note']) ? $payment['payment_note'] : null,
                                $debit_account_id, $credit_account_id, 1, $payment_status, $perent_transaction_id, null, $is_opening_bl);

          //Udpate Unadjusted
        if($Account_transaction!='error'){
            if((in_array($credit_account_id, [$df_debtor_account_id, $df_creditor_account_id])) || 
               (in_array($debit_account_id, [$df_debtor_account_id, $df_creditor_account_id]))){
                 $this->updateUnAdjustedAmount($Account_transaction->id);
               }    

            
        }       
        
        if($credit_account_id == $df_debtor_account_id){
            foreach($reference_lines as $reference_line ){
                $this->updateCreditorPaymentStatus($reference_line['perent_transaction_id']);
            } 
        }

        if($debit_account_id == $df_creditor_account_id){
            foreach($reference_lines as $reference_line ){
                $this->updateDebtorPaymentStatus($reference_line['perent_transaction_id']);
            } 
        }
                            

         return  $Account_transaction;
        // }
               

    }

    public function createCreditNote($business_id, $transaction, $payments)
    {   

        $sale_transaction = DoubleEntryTransaction::where('sys_transaction_id', $transaction['sys_transaction_id'])->first();


        $reference_line=[];
        $reference_line['ref_no'] = $transaction['invoice_no'];
        $reference_line['desc'] = null;
        $reference_line['perent_transaction_id'] = isset($sale_transaction) ? $sale_transaction->id : null;
        $reference_line['sub_amount'] = null;

        $reference_lines[]= $reference_line;

        $debit_account_id = config('accounting.default_account_ids.sales');
        $credit_account_id = config('accounting.default_account_ids.debtor');


        $Credit_Note_transaction =  $this->CreateTransaction($business_id, $transaction['transaction_id'],
            $transaction['location_id'],$transaction['contact_id'],$reference_lines, 'CDN', null,
            $transaction['transaction_date'],$transaction['total_amount'],
            null,null,null,null,null, $debit_account_id, $credit_account_id, 1, 'due',null, $transaction['sys_transaction_id']);
        
        if($Credit_Note_transaction!='error'){

            $has_transaction = DoubleEntryTransaction::where('perent_transaction_id', $Credit_Note_transaction->id)
                                                            ->where('document_type','COSR')
                                                            ->where('is_canceled',0)
                                                          ->first();

            if(isset($transaction['total_cost'])){                 
                
                    $transaction_id_cosr=null;                                         
                    if(isset($has_transaction)){
                        $transaction_id_cosr = $has_transaction->id;
                    }
               
                $cos_credit_account_id = config('accounting.default_account_ids.cost_of_sale');
                $cos_debit_account_id = config('accounting.default_account_ids.inventory');
                 
                if(($transaction['total_cost']!=null) || (isset($has_transaction))){
                    $restore_cost_of_sale =  $this->CreateTransaction($business_id, $transaction_id_cosr, $transaction['location_id'], null, $reference_lines, 'COSR',$Credit_Note_transaction->document_no, $transaction['transaction_date'], $transaction['total_cost'],
                    null,null,null,null,null, $cos_debit_account_id, $cos_credit_account_id, 1, null, $Credit_Note_transaction->id);
                }
               
                                               
            } 

            if(isset($payments)){
                foreach($payments as $payment){

                    $credit_account_id = null;
        
                    $debit_account_id = config('accounting.default_account_ids.debtor');
                    
                    if(isset($payment['credit_account_id'])) {
                        $credit_account_id = $payment['credit_account_id'];
                    }else
                    {   
                        if($payment['payment_method']=='cash'){
                            $credit_account_id = config('accounting.default_account_ids.cash_in_hand');                
                        }else if($payment['payment_method']=='cheque'){
                            $credit_account_id = config('accounting.default_account_ids.cheque_in_hand');                
                        }else if($payment['payment_method']=='card'){
                            $credit_account_id = config('accounting.default_account_ids.card_payment');                
                        }else if($payment['payment_method']=='other'){
                            $credit_account_id = config('accounting.default_account_ids.other_payment');                
                        }
                    }
                            
                    $Account_transaction =  $this->CreateTransaction($business_id,  null,
                                            $location_id, $contact_id, $reference_lines, 'CNP', null, $payment['transaction_date'],$payment['total_amount'],
                                            isset($payment['payment_method']) ? $payment['payment_method'] : null,
                                            isset($payment['cheque_no']) ? $payment['cheque_no'] : null,
                                            isset($payment['payee']) ? $payment['payee'] : null,
                                            isset($payment['cheque_date']) ? $payment['cheque_date'] : null,
                                            isset($payment['payment_note']) ? $payment['payment_note'] : null,
                                            $debit_account_id, $credit_account_id, 1, null , $Credit_Note_transaction->id);
                    
                    //Udpate Unadjusted
                    // if($Account_transaction!='error'){
                    //     $this->updateUnAdjustedAmount($Account_transaction->id);
                    // }        
            
                    foreach($reference_lines as $reference_line ){
                        $this->updateDebtorPaymentStatus($Credit_Note_transaction->id);
                    }
                    
            
                   // return  $Account_transaction;
                }
            }

            return 'DONE';
        }else{
            return 'error';
        }

        

    }

    public function createDebitNote($business_id, $transaction, $payments)
    {   

        $purchase_transaction = DoubleEntryTransaction::where('sys_transaction_id', $transaction['sys_transaction_id'])->first();
        
        $reference_line=[];
        $reference_line['ref_no'] = $transaction['purchase_ref'];
        $reference_line['desc'] = null;
        $reference_line['perent_transaction_id'] = isset($purchase_transaction) ? $purchase_transaction->id : null;
        $reference_line['sub_amount'] = null;

        $reference_lines[] = $reference_line;

        $debit_account_id = config('accounting.default_account_ids.creditor');
        $credit_account_id = config('accounting.default_account_ids.inventory');


        $Debit_Note_transaction =  $this->CreateTransaction($business_id,  null,
            $transaction['location_id'],$transaction['contact_id'],$reference_lines, 'DBN', null,
            $transaction['transaction_date'],$transaction['total_amount'],
            null,null,null,null,null, $debit_account_id, $credit_account_id, 1, null,null, $transaction['sys_transaction_id']);
            
        if($Debit_Note_transaction!='error'){

           /*
            if(isset($payments)){
                foreach($payments as $payment){

                    $debit_account_id = null;
        
                    $credit_account_id = config('accounting.default_account_ids.creditor');
                    
                    if(isset($payment['credit_account_id'])) {
                        $debit_account_id = $payment['credit_account_id'];
                    }else
                    {   
                        if($payment['payment_method']=='cash'){
                            $debit_account_id = config('accounting.default_account_ids.cash_in_hand');                
                        }else if($payment['payment_method']=='cheque'){
                            $debit_account_id = config('accounting.default_account_ids.cheque_in_hand');                
                        }else if($payment['payment_method']=='card'){
                            $debit_account_id = config('accounting.default_account_ids.card_payment');                
                        }else if($payment['payment_method']=='other'){
                            $debit_account_id = config('accounting.default_account_ids.other_payment');                
                        }
                    }
                            
                    $Account_transaction =  $this->CreateTransaction($business_id,  null,
                                            $location_id, $contact_id, $reference_lines, 'DNP', null, $payment['transaction_date'],$payment['total_amount'],
                                            isset($payment['payment_method']) ? $payment['payment_method'] : null,
                                            isset($payment['cheque_no']) ? $payment['cheque_no'] : null,
                                            isset($payment['payee']) ? $payment['payee'] : null,
                                            isset($payment['cheque_date']) ? $payment['cheque_date'] : null,
                                            isset($payment['payment_note']) ? $payment['payment_note'] : null,
                                            $debit_account_id, $credit_account_id, 1, null , $Credit_Note_transaction->id);
                    
                    //Udpate Unadjusted
                    // if($Account_transaction!='error'){
                    //     $this->updateUnAdjustedAmount($Account_transaction->id);
                    // }        
            
                    foreach($reference_lines as $reference_line ){
                        $this->updateCreditorPaymentStatus($Debit_Note_transaction->id);
                    }
                    
            
                   // return  $Account_transaction;
                }
            } */

            return 'DONE';
        }else{
            return 'error';
        }

        

    }

    public function createBankDeposit($business_id, $transaction, $receipts_list)
    {
         
        $credit_account_id = $transaction['credit_account_id'];
        $debit_account_id = $transaction['debit_account_id'];

        $reference_lines=[];
        
        $has_error =0;
        $debit_total_amount =0;

        if($transaction['is_single']==1){
            
            $first_pmt_method='';
            $first_cheque_no=null;
            $first_cheque_date=null;
             

            $count = 0;

            foreach($receipts_list as $receipt){
                if($count==0){

                    $first_pmt_method = $receipt['payment_method'];                    
                    if($receipt['payment_method'] == 'cheque'){
                        $first_cheque_no = $receipt['cheque_no'];
                        $first_cheque_date = $receipt['cheque_date'];

                    }

                    $count++;
                }

                if($first_pmt_method == $receipt['payment_method']){
                    if($first_pmt_method=='cheque'){
                        if($first_cheque_no == $receipt['cheque_no']){
                            $reference_lines[] = ['perent_transaction_id'=>$receipt['perent_transaction_id'],
                                                   'ref_no'=>$receipt['ref_no'],'sub_amount'=>$receipt['sub_amount']];
                            $debit_total_amount += $receipt['sub_amount'];
                        }
                    }else{
                        $reference_lines[] = ['perent_transaction_id'=>$receipt['perent_transaction_id'],
                                              'ref_no'=>$receipt['ref_no'],'sub_amount'=>$receipt['sub_amount']];
                            $debit_total_amount += $receipt['sub_amount'];
                    }
                }else{
                    $has_error++;
                }

            }

            if($has_error==0){

                $Account_transaction =  $this->CreateTransaction($business_id,  null,
                    null, null, [], 'BDS', null,
                    $transaction['transaction_date'], $debit_total_amount,
                    null,$first_cheque_no,null, isset($first_cheque_date) ? date("Y-m-d", strtotime($first_cheque_date)) : null, $transaction['payment_note'], $debit_account_id, $credit_account_id, 1, null, null, null);

                    //$Account_transaction->
                foreach($reference_lines as $ref){
                    $this->updateTransactionStatusOnDeposit($ref['perent_transaction_id'],2, $Account_transaction->id, $first_cheque_no);
                }

            }else{
                return 'error';
            }

        }else{

            foreach($receipts_list as $receipt){
                
               // $reference_line = ['perent_transaction_id'=>$receipt['perent_transaction_id'], 'ref_no'=> (isset($receipt['ref_no']) ? $receipt['ref_no'] : null),'sub_amount'=>$receipt['sub_amount']];

                //$reference_lines[]=$reference_line;

                $Account_transaction =  $this->CreateTransaction($business_id,  null,
                        null, null, [], 'BDS', null,
                        $transaction['transaction_date'], $receipt['sub_amount'],
                        $receipt['payment_method'], $receipt['cheque_no'],null,
                        isset($receipt['cheque_date']) ? date("Y-m-d", strtotime($receipt['cheque_date'])) : null,
                        $transaction['payment_note'], $debit_account_id, $credit_account_id, 1, null,null, null);
                
                 $this->updateTransactionStatusOnDeposit($receipt['perent_transaction_id'],2, $Account_transaction->id, $receipt['cheque_no']);

            }
        }

        return 'Done';



    }

    public function updateTransactionStatusOnDeposit($transaction_id, $status,$deposited_transaction_id, $cheque_no=null)
    {   
        $transaction = DoubleEntryTransaction::where('id',$transaction_id)->where('is_canceled',0)->first();
        
        if(isset($transaction)){
            $transaction->status = $status;
            $transaction->deposit_transaction_id = $deposited_transaction_id;
            if($cheque_no!=null){
                $transaction->cheque_no = $cheque_no;
            }
            $transaction->save();
            return $transaction;
        }else{
            return null;
        }
    }

    public function updateDebtorPaymentStatus($perent_transaction_id)
    {
         $transaction = DoubleEntryTransaction::find($perent_transaction_id);

        
         $total_paid = 0;

        //  if($transaction->payment_status != 'patial'){
            $total_paid = DoubleEntryTransaction::join('acc_transaction_details AS ATD','ATD.transaction_id','=','acc_transactions.id')
                                                ->where('ATD.perent_transaction_id', $transaction->id)
                                                ->whereNotNull('acc_transactions.payment_method')
                                                ->whereIn('acc_transactions.document_type',[
                                                    config('accounting.document_type_prefix.receipt'),
                                                    config('accounting.document_type_prefix.petty_cash_vc'),
                                                    config('accounting.document_type_prefix.bank_debit_ad'),
                                                    // config('accounting.document_type_prefix.bank_credit_ad'),
                                                    config('accounting.document_type_prefix.journal_vo')
                                                    ])
                                                ->where('acc_transactions.is_canceled', 0)  
                                                ->sum('ATD.sub_amount');
                                                                              

        //  }

         if(isset($transaction)){
           
             if($transaction->total_amount  ==  ($total_paid)){
                
                $transaction->payment_status ='paid';
                $transaction->save();
               // return 'done';
             }else if(($transaction->total_amount > ($total_paid) && ($total_paid > 0))) {
                $transaction->payment_status ='patial';
                $transaction->save();
               // return 'done';
             }else if(($transaction->total_amount > 0) && ($total_paid==0)){
                $transaction->payment_status ='due';
                $transaction->save();
             }

             if($transaction->total_amount  ==  0 ){
                
                $transaction->payment_status ='paid';
                $transaction->save();
               // return 'done';
             }
         }

    }

    public function updateCreditorPaymentStatus($perent_transaction_id)
    {
         $transaction = DoubleEntryTransaction::find($perent_transaction_id);

         
         $total_paid = 0;

        //  if($transaction->payment_status == 'patial'){
            $total_paid = DoubleEntryTransaction::join('acc_transaction_details AS ATD','ATD.transaction_id','=','acc_transactions.id')
                                                ->where('ATD.perent_transaction_id', $transaction->id)
                                                ->whereNotNull('acc_transactions.payment_method')
                                                ->whereIn('acc_transactions.document_type',[
                                                    config('accounting.document_type_prefix.cheque_payment_vc'),
                                                    config('accounting.document_type_prefix.petty_cash_vc'),
                                                    // config('accounting.document_type_prefix.bank_debit_ad'),
                                                    config('accounting.document_type_prefix.bank_credit_ad'),
                                                    config('accounting.document_type_prefix.journal_vo')
                                                    ])
                                                ->where('acc_transactions.is_canceled', 0)  
                                                ->sum('ATD.sub_amount');
                                                                              

        //  }

        //  if(isset($transaction)){
           
        //      if($transaction->total_amount  ==  ($total_paid)){
                
        //         $transaction->payment_status ='paid';
        //         $transaction->save();
        //        // return 'done';
        //      }else if($transaction->total_amount > ($total_paid)) {
        //         $transaction->payment_status ='patial';
        //         $transaction->save();
        //        // return 'done';
        //      }
        //  }
        if(isset($transaction)){
           
            if($transaction->total_amount  ==  ($total_paid)){
               
               $transaction->payment_status ='paid';
               $transaction->save();
              // return 'done';
            }else if(($transaction->total_amount > ($total_paid) && ($total_paid > 0))) {
               $transaction->payment_status ='patial';
               $transaction->save();
              // return 'done';
            }else if(($transaction->total_amount > 0) && ($total_paid==0)){
               $transaction->payment_status ='due';
               $transaction->save();
            }

            if($transaction->total_amount  ==  0 ){
               
               $transaction->payment_status ='paid';
               $transaction->save();
              // return 'done';
            }
        }

    }
    
    public function updateCosOfSale($business_id, $location_id,$transaction,$reference_line,$perent_transaction_id, $reference_no)
    {
        $credit_account_id = config('accounting.default_account_ids.inventory');
        $debit_account_id = config('accounting.default_account_ids.cost_of_sale');

        $transaction_id =null;
        $has_transaction = DoubleEntryTransaction::where('perent_transaction_id',$perent_transaction_id)
                         ->where('document_type','COS')
                         ->where('status',1)
                         ->where('is_canceled',0)->first();

        $transaction_id  = isset($has_transaction) ? $has_transaction->id : null;

        $Account_transaction =  $this->CreateTransaction($business_id, $transaction_id, $location_id, null, $reference_line, 'COS', $reference_no , $transaction['transaction_date'], $transaction['total_cost'],
                                null,null,null,null,null, $debit_account_id, $credit_account_id, 1, null, $perent_transaction_id);

        return $Account_transaction;
    }

    public function CreateTransaction($business_id,$transaction_id, $location_id, $vendor_id, $reference_line , $document_type, $reference_no, $transaction_date, 
                                    $total_amount, $payment_method, $cheque_no, $payee, $cheque_date, $payment_note, $debit_account_id, $credit_account_id,$status, $payment_status , $perent_transaction_id=null, $sys_transaction_id=null, $is_opening_bl=null)
    {   
       // try{    

          //  DB::beginTransaction();

            if(isset($transaction_id)){
                $newTransaction = DoubleEntryTransaction::find($transaction_id);

                if(!isset($newTransaction)){
                    return 'error';
                }
                
            }else{

                // Get Transaction Reffrence no 
                if(!(isset($reference_no))){
                    $reference_no = $this->getNewTransactionNoByType($business_id, $document_type, $transaction_date);
                }  

                //Create new Transaction
                $newTransaction = new DoubleEntryTransaction();
            }
                        

            $newTransaction->business_id = $business_id;
            $newTransaction->location_id = $location_id;
           
            if(!isset($transaction_id)){
                $newTransaction->added_by = Auth::user()->id;
                $newTransaction->document_no = $reference_no;
                
                $newTransaction->document_type = $document_type;
            }
            

            $newTransaction->vendor_id = $vendor_id;            
            
            
            $newTransaction->transaction_date = $transaction_date;
            $newTransaction->payment_method = $payment_method;
            $newTransaction->total_amount = $total_amount;

            $newTransaction->cheque_no = $cheque_no;
            $newTransaction->payee = $payee;
            $newTransaction->cheque_date = $cheque_date;

            $newTransaction->payment_note = $payment_note;
            $newTransaction->perent_transaction_id = $perent_transaction_id;
            $newTransaction->sys_transaction_id = $sys_transaction_id;
            $newTransaction->is_opening_bl = isset($is_opening_bl) ? $is_opening_bl : 0;
            
            $newTransaction->period = $this->getCurrentFinancialYear($business_id, $transaction_date);
 
            if($payment_status!=null){
                $newTransaction->payment_status=$payment_status;
            }

            $newTransaction->is_rec = 0;
            $newTransaction->is_canceled =0;
            $newTransaction->is_print =0;
            $newTransaction->is_print_chq = 0;
            $newTransaction->status = $status;
            $newTransaction->save();
            
            if(isset($transaction_id)){
                $newTransaction->transactionDetails()->delete();
                $newTransaction->ledgerTransactions()->where('entry_type','DR')->update(['account_id'=>$debit_account_id,'amount'=>$total_amount]);
                $newTransaction->ledgerTransactions()->where('entry_type','CR')->update(['account_id'=>$credit_account_id,'amount'=>$total_amount]);

            }else{
                //Create Ledger Entry (Debit/Credit entries)
                $newTransaction->ledgerTransactions()->createMany([
                    ['account_id'=>$debit_account_id,'entry_type'=>'DR','amount'=>$total_amount],
                    ['account_id'=>$credit_account_id,'entry_type'=>'CR','amount'=>$total_amount]
                ]);
            }

            

            //Create Transaction Details
            if(count($reference_line)>0){
                $newTransaction->transactionDetails()->createMany($reference_line);
            }
            
            //Update Account Balances on CR and DR
            $this->updateBalanceOnTransaction($debit_account_id);
            $this->updateBalanceOnTransaction($credit_account_id);
            
          //  DB::commit();

            return $newTransaction;

      //  }catch(\Exception $e){
          //   DB::rollback();            
          //   Log::error($e);
          
          //  return 'error';
    //    }
        
        
    }

    public function removeTransaction($id)
    {   
        
        try{

            DB::beginTransaction();
            //Find Perent Transaction
            $transaction = DoubleEntryTransaction::find($id);
            //Find Child Transaction
            $child_transactions = DoubleEntryTransaction::where('perent_transaction_id',$id)->get();

            //Check has Child Transaction 
            if(isset($child_transactions)){
                
                //Remove Child Transactions
                foreach($child_transactions as $child_trans){
                    $child_trans->ledgerTransactions()->delete();
                    $child_trans->transactionDetails()->delete();
                    $child_trans->delete();
                }
            }

            //Remove Perent Transaction
            $transaction->ledgerTransactions()->delete();
            $transaction->transactionDetails()->delete();
            $transaction->delete();

            DB::commit();

            return 'done';

        }catch(\Exception $e){
            DB::rollback();            
            Log::error($e);
            return 'error';
        }

    }

    public function updateBalanceOnTransaction($account_id)
    {
         $account = DoubleEntryAccount::find($account_id);

         if(isset($account)){
            $account->balance = $account->getBalance();
            $account->save();

            return $account->balance;
         }

         return null;
         
    }

    public function updateUnAdjustedAmount($transaction_id)
    {
        $transaction = DoubleEntryTransaction::where('id',$transaction_id)
                     ->whereIn('document_type',[
                        config('accounting.document_type_prefix.receipt'),
                        config('accounting.document_type_prefix.petty_cash_vc'),
                        config('accounting.document_type_prefix.cheque_payment_vc'),
                        config('accounting.document_type_prefix.journal_vo'),
                        // config('accounting.document_type_prefix.bank_debit_ad'),
                        // config('accounting.document_type_prefix.bank_credit_ad'),
                        // config('accounting.document_type_prefix.bank_credit_ad'),
                     ])->first();
        $total_adjusted_amount = null;
        if(isset($transaction)){
            if($transaction->is_opening_bl==0 && $transaction->vendor_id!=null ){
                $total_adjusted_amount = $transaction->transactionDetails()->sum('sub_amount');                        
                $total_unadjusted_amount = $transaction->total_amount - $total_adjusted_amount;             
                $transaction->total_unaj_amount = $total_unadjusted_amount;
                $transaction->save();    
            }
                    
        }
       
    }

    public function getNewTransactionNoByType($business_id, $document_type, $transaction_date)
    {   
       //DB::beginTransaction();
        $doc_no='';
        //$current_year = date("Y");
        $current_year = $this->getCurrentFinancialYear($business_id, $transaction_date);
        $schema = DoubleEntryTransactionSchemes::where('name',$document_type)->where('year', $current_year)->first();
        
            if(!isset($schema)){
                $desc = '';
                if($document_type=='PCV'){
                    $schema_prefix = config('accounting.document_type_prefix.petty_cash_vc');
                    $desc = 'Petty Cash Voucher';
                }else if($document_type=='CPV'){
                    $schema_prefix = config('accounting.document_type_prefix.cheque_payment_vc');
                    $desc = 'Cheque Payment Voucher';
                }else if($document_type=='BDA'){
                    $schema_prefix = config('accounting.document_type_prefix.bank_debit_ad');
                    $desc = 'Bank Debit Advisor';
                }else if($document_type=='BCA'){
                    $schema_prefix = config('accounting.document_type_prefix.bank_credit_ad');
                    $desc = 'Bank Credit Advisor';
                }else if($document_type=='JNV'){
                    $schema_prefix = config('accounting.document_type_prefix.journal_vo');
                    $desc = 'Journal Voucher';
                }else if($document_type=='SAL'){
                    $schema_prefix = config('accounting.document_type_prefix.sales');
                    $desc = 'Sales Invoice';
                }else if($document_type=='GRN'){
                    $schema_prefix = config('accounting.document_type_prefix.grn');
                    $desc = 'GRN';
                }else if($document_type=='EXP'){
                    $schema_prefix = config('accounting.document_type_prefix.expenses');
                    $desc = 'GRN';
                }else if($document_type=='RCP'){
                    $schema_prefix = config('accounting.document_type_prefix.receipt');
                    $desc = 'Receipt';
                }else if($document_type=='CRN'){
                    $schema_prefix = config('accounting.document_type_prefix.cheque_return_note');
                    $desc = 'Cheque Return Note';
                }else if($document_type=='CDN'){
                    $schema_prefix = config('accounting.document_type_prefix.credit_note');
                    $desc = 'Credit Note';
                }else if($document_type=='CNP'){
                    $schema_prefix = config('accounting.document_type_prefix.credit_note_payment');
                    $desc = 'Credit Note Payment';
                }else if($document_type=='DBN'){
                    $schema_prefix = config('accounting.document_type_prefix.debit_note');
                    $desc = 'Debit Note';
                }else if($document_type=='DNP'){
                    $schema_prefix = config('accounting.document_type_prefix.debit_note_payment');
                    $desc = 'Debit Note Payment';
                }else if($document_type=='BDS'){
                    $schema_prefix = config('accounting.document_type_prefix.bank_deposit');
                    $desc = 'Bank Depositt';
                }else{
                    return null;
                }

                $schema = new DoubleEntryTransactionSchemes();
                $schema->business_id = $business_id;
                $schema->name = $schema_prefix;
                $schema->desc = $desc;
                $schema->prefix = $schema_prefix;
                $schema->start_number = 0;
                $schema->count = 0;
                $schema->digit = 4;
                $schema->year = $current_year;
                $schema->save();            
            } 


        $schema->count = ($schema->count+1);
        $schema->save();
        
        $count_no = $schema->count;
        $num_ber = $count_no;
            
        if((strlen((string)$count_no)) < 4){
            $num_ber = sprintf("%04d", ($count_no));
        }else{
            $num_ber=$count_no;
        }       

        $doc_no = $schema->prefix.$schema->year.'/'.$num_ber;
        
        //DB::commit();
        return $doc_no;
    }

    public function getCurrentFinancialYear($business_id, $transaction_date)
    {
       $staring_month = Business::find($business_id);
       $staring_month = $staring_month->fy_start_month;
       $year = date("Y", strtotime($transaction_date));
       $month = date("m", strtotime($transaction_date));
        
       if($month < $staring_month){
        return $year-1;
       }else{
           return $year;
       }
    }

    public function mapExpenseAccountByCategory($id)
    {
        $expense_category = ExpenseCategory::find($id);
        if(isset($expense_category)){
            return isset($expense_category->account_id) ? $expense_category->account_id : null;
        }else{
           return null;
        }
    }

}
