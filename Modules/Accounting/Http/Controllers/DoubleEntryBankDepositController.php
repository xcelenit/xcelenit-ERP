<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\Utility\Utility;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryTransaction;
use Modules\Accounting\Entities\Utility\AccountUtility;
use Modules\Accounting\Entities\Utility\AccountTransactionUtility;

class DoubleEntryBankDepositController extends Controller
{
     
    protected $accountUtility;
    protected $utility;
    protected $accountTransactionutility;
    protected $transactionUtil;
    
    public function __construct(Utility $utility, AccountUtility $accountUtility,AccountTransactionUtility $accountTransactionutility) {
        $this->utility = $utility;
        $this->accountUtility = $accountUtility;        
        $this->accountTransactionutility = $accountTransactionutility;

    }
  
    public function index()
    {   

       // $cash_in_hand_account_list = DoubleEntryAccount::whereIn('id',[config('accounting.default_account_ids.cash_in_hand')])->where('is_active',1)->get();
        $cheque_in_hand_account_list = DoubleEntryAccount::whereIn('id',[config('accounting.default_account_ids.cheque_in_hand'),config('accounting.default_account_ids.cash_in_hand')])->where('is_active',1)->get();

         $cash_in_hand_accounts=[];
        $cheque_in_hand_accounts=[];

        

        foreach($cheque_in_hand_account_list as $account){

            $balance = $account->getBalance();
            $cheque_in_hand_accounts[] = ['id'=>$account->id,'account_code'=>$account->account_code, 
                                        'account_name'=>$account->account_name.' ('.$account->account_no.')' , 
                                        'balance'=> isset($balance) ? $balance : 0.00];
        }

        $document_type_code = config('accounting.document_type_prefix.bank_deposit');
        $credit_account_list = DoubleEntryAccount::getBankAccountList();
        

        return view('accounting::deposit.index',compact('cash_in_hand_accounts','cheque_in_hand_accounts','document_type_code','credit_account_list'));
    }
    
    public function getData(Request $request)
    {
        //
        //return $request->all();
        $account_id = $request->account_id;
        $account = DoubleEntryAccount::find($account_id);
        
        if(isset($account)){
            $transaction_data = $account->ledgerTransactions()->join('acc_transactions as AT','AT.id','=','acc_ledger_transactions.transaction_id')
                                           ->leftJoin('contacts as customer','customer.id','=','AT.vendor_id')
                                           ->with('transactionDetails')
                                           ->where('AT.is_canceled',0)
                                           ->where('AT.status',1)
                                           ->where('acc_ledger_transactions.entry_type','DR');

                                           if (!empty($request->start_date) && !empty($request->end_date)) {
                                            $start = $request->start_date;
                                            $end =  $request->end_date;
                                            $transaction_data->whereDate(DB::raw('date(transaction_date)'), '>=', $start)
                                                            ->whereDate(DB::raw('date(transaction_date)'), '<=', $end);
                                          }

            $transactions = $transaction_data->select('AT.*','acc_ledger_transactions.transaction_id','customer.name as customer_name')
                                           ->orderBy('AT.transaction_date','ASC')
                                           ->get();
            
            //return $transactions;
           $html ='';

           foreach($transactions as $trans){
            
            $transaction_inv_nos =' / ';
            $count=0;
            $transactionDetails = $trans->transactionDetails()->get();

            foreach($transactionDetails as $inv){
                $count++;
                $transaction_inv_nos .='<span class="text-info"> <a href="#">'.$inv->ref_no.'</a></span>';
                if(count($transactionDetails) > $count){
                    $transaction_inv_nos .=',';
                }
            }

            $cheque_no ='-';
             if($trans->payment_method=='cheque'){
                $cheque_no = '<input type="text" style="width:100%;" class="cheque_no" value="'.(isset($trans->cheque_no) ? $trans->cheque_no : null).'">';
             }else{
                $cheque_no .='<input type="hidden" class="cheque_no" value="">';
             }

            $html .='
                        <tr>
                             <td class="text-center" >
                                <input type="checkbox" onClick="selectRow()" class="transaction_id"  value="'.$trans->id.'">
                                <input type="hidden" class="amount" value="'.number_format($trans->total_amount,2,'.','').'">
                                <input type="hidden" class="payment_method" value="'.$trans->payment_method.'">
                                <input type="hidden" class="document_no" value="'.$trans->document_no.'">
                                <input type="hidden" class="cheque_date" value="'.$trans->cheque_date.'">
                    
                             </td>
                             <td class="text-center">
                               '.$cheque_no.'
                             </td>
                             <td>'.strtoupper($trans->payment_method).' | '.$trans->document_no.' - '.$trans->customer_name.$transaction_inv_nos.'</td>
                             <td width="15%">'.$trans->transaction_date.'</td>                             
                             <td colspan="2"  class="text-right"><b>'.number_format($trans->total_amount,2,'.',',').'</b></td>                             
                         </tr>';
           }

           return $html;
        }
        
        return null;

    }
    
    public function store(Request $request)
    {
        //
         
       // return $request->all();

        try{
            $business_id = $request->session()->get('user.business_id');

            $transaction = [
                'transaction_date' => date("Y-m-d H:i:s",strtotime($request->transaction_date)),
                'credit_account_id'=>$request->credit_account_id,
                'debit_account_id'=>$request->debit_account_id,
                'is_single'=>$request->is_single,
                'payment_note'=>$request->payment_note,
            ];
            
            DB::beginTransaction();

            $this->accountTransactionutility->createBankDeposit($business_id, $transaction, $request->details_list);

            DB::commit();

            return 'done';

        }catch(Exception $ex){
            DB::rollback();
            \Log::error($ex);
            return 'error';
        }
    }
     
}
