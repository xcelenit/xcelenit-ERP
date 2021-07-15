<?php

namespace Modules\Accounting\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryTransaction;
use Modules\Accounting\Entities\DoubleEntryLedgerTransaction;
use Modules\Accounting\Entities\Utility\AccountTransactionUtility;

class BankReconcilationController extends Controller
{   
    protected $accountTransactionutility;
    
    public function __construct(AccountTransactionUtility $accountTransactionutility) {
              
        $this->accountTransactionutility = $accountTransactionutility;

    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {   
        $account_list = DoubleEntryAccount::getBankAccountList();
        $years=['2020'=>'2020','2021'=>'2021'];
        $months=['1'=>'JAN','2'=>'FEB','3'=>'MAR','4'=>'APR','5'=>'MAY','6'=>'JUN','7'=>'JUL','8'=>'AUG','9'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DEC'];
        return view('accounting::bank_reconcilation.index',
            compact('account_list','years','months'));
    }

     
    public function getRecData(Request $request)
    {
        //
        //return $request->all();
        $account_id = $request->account_id;
        $year = $request->year;
        $month = $request->month;
        $entry_type = $request->entry_type;
        $status = $request->status;
        //return  $request->entry_type;

        $first_day_of_month = $year.'-'.$month.'-1';

        $endOfMonth = Carbon::createFromFormat('Y-m-d', $first_day_of_month)   
                        ->endOfMonth()   
                        ->format('Y-m-d');
         

        $account = DoubleEntryAccount::find($account_id);
        
        if(isset($account)){
            $transaction_data = $account->ledgerTransactions()->join('acc_transactions as AT','AT.id','=','acc_ledger_transactions.transaction_id')
                                           ->leftJoin('contacts as customer','customer.id','=','AT.vendor_id')
                                        //    ->where('is_opening_bl',0)
                                           ->where('AT.is_canceled',0)
                                        //    ->where('AT.status',1)
                                           ->where('acc_ledger_transactions.entry_type', $entry_type);

                                           $transaction_data->whereRaw('IF(acc_ledger_transactions.is_reconcile=0, DATE(AT.transaction_date) <= ? , DATE(acc_ledger_transactions.reconcile_at) BETWEEN ? AND ? )',[ $endOfMonth,$first_day_of_month,$endOfMonth]);
                                        //    ->whereYear(DB::raw('date(IF(acc_ledger_transactions.is_reconcile=0,"AT.transaction_date","acc_ledger_transactions.reconcile_at")'), '=', $year)
                                        //    ->whereMonth(DB::raw('date(transaction_date)'), '=', $month);

                                           if($status!='all'){
                                                $transaction_data->where('acc_ledger_transactions.is_reconcile',$status);
                                           }

            $transactions = $transaction_data->select('AT.*','acc_ledger_transactions.id as acc_ledger_transaction_id','acc_ledger_transactions.is_reconcile',
                                                      'acc_ledger_transactions.transaction_id','acc_ledger_transactions.entry_type','acc_ledger_transactions.amount',
                                                      'acc_ledger_transactions.reconcile_at','customer.name as customer_name')
                                           ->orderBy('AT.transaction_date','ASC')
                                           ->get();
            
           // return $transactions;
          

           $transactions_not_reconciled_data = $account->ledgerTransactions()->join('acc_transactions as AT','AT.id','=','acc_ledger_transactions.transaction_id')
                                    ->leftJoin('contacts as customer','customer.id','=','AT.vendor_id')
                                    // ->where('is_opening_bl',0)                                    
                                    ->where('AT.is_canceled',0)                                    
                                    ->where(DB::raw('date(transaction_date)'), '<=', $endOfMonth)
                                    ->whereRaw('IF(acc_ledger_transactions.is_reconcile=0, acc_ledger_transactions.reconcile_at is null, DATE(acc_ledger_transactions.reconcile_at) > ? )',[$endOfMonth]);

        $transactions_not_reconciled = $transactions_not_reconciled_data->select('AT.*','acc_ledger_transactions.id as acc_ledger_transaction_id','acc_ledger_transactions.is_reconcile','acc_ledger_transactions.transaction_id','acc_ledger_transactions.entry_type','acc_ledger_transactions.amount','customer.name as customer_name')
                                        ->orderBy('AT.transaction_date','ASC')
                                        ->get();
            

           $dr_not_realised_list ='';
           $dr_not_realised_count =0;
           $dr_not_realised_total =0;

           $cr_not_presented_list ='';
           $cr_not_presented_count =0;
           $cr_not_presented_total =0;
           $ac_closing_balance = $account->getBalanceAsAtDate(null,$endOfMonth);
           $bank_balance = $ac_closing_balance;
            
           foreach($transactions_not_reconciled as $trans_ntrec){
            if($trans_ntrec->entry_type=='DR'){
                $dr_not_realised_list .= '
                    <tr class="'.($trans_ntrec->is_reconcile==1 ? 'bg-success' : '').'" >
                        <td class="text-center">'.$trans_ntrec->transaction_date.'</td>
                        <td>'.$trans_ntrec->document_no.'</td>
                        <td>'.$trans_ntrec->payment_note.(isset($trans->customer_name) ? '| Vendor : '.$trans_ntrec->customer_name : '').'</td>
                        <td class="text-center">'.$trans_ntrec->cheque_no.'</td>
                        <td class="text-right">'.number_format($trans_ntrec->amount,2,'.',',').'</td>
                    </tr>';
                    $bank_balance -=$trans_ntrec->amount;
                    $dr_not_realised_count ++;
                    $dr_not_realised_total +=$trans_ntrec->amount;
            }else{
                $cr_not_presented_list .='
                    <tr class="'.($trans_ntrec->is_reconcile==1 ? 'bg-success' : '').'">
                        <td class="text-center">'.$trans_ntrec->transaction_date.'</td>
                        <td>'.$trans_ntrec->document_no.'</td>
                        <td>'.$trans_ntrec->payment_note.(isset($trans->customer_name) ? '| Vendor : '.$trans_ntrec->customer_name : '').'</td>
                        <td class="text-center">'.$trans_ntrec->cheque_no.'</td>
                        <td class="text-right">'.number_format($trans_ntrec->amount,2,'.',',').'</td>
                    </tr>';
                    $bank_balance +=$trans_ntrec->amount;
                    $cr_not_presented_count ++;
                    $cr_not_presented_total +=$trans_ntrec->amount;
            }
           }

           $html ='';

           
           foreach($transactions as $trans){



            $html .='<tr class="'.($trans->is_reconcile==1 ? 'bg-success' : '').'" >
                    <td>'.$trans->transaction_date.'</td>
                    <td>'.$trans->document_no.'</td>
                    <td>'.$trans->payment_note.(isset($trans->customer_name) ? '| Vendor : '.$trans->customer_name : '').'
                        <input type="hidden" class="amount" value="'.number_format($trans->total_amount,2,'.','').'">
                    </td>
                    <td class="text-center">'.$trans->cheque_no.'</td>
                    <td class="text-right">'.($trans->entry_type=='DR' ? number_format($trans->amount,2,'.',',') : '').'</td>
                    <td class="text-right" style="padding: 0px;">'.($trans->entry_type=='CR' ? '<input onchange="selectRow()" type="text" class="text-right cr_amount" '.(($trans->amount > 2) ? 'disabled' : '' ) .' value="'.number_format($trans->amount,2,'.','').'" />' : '').'</td>
                    <td class="text-center">
                        <input type="checkbox" onClick="selectRow()" '.($trans->is_reconcile==1 ? 'checked="true"' : '').' class="transaction_id" value="'.$trans->acc_ledger_transaction_id.'"/>
                    </td>
                    <td class="text-center">'.$trans->period.'</td>
                    <td class="text-center">'.$trans->document_type.'</td>
                    <td class="text-center">'.$trans->reconcile_at.'</td>    
                </tr> ';
           }

           $total_no_of_to_be_rec = $account->ledgerTransactions()->join('acc_transactions as AT','AT.id','=','acc_ledger_transactions.transaction_id')
                                            ->where('AT.is_canceled',0)->where('acc_ledger_transactions.is_reconcile',0)->count();

           return [
               'status'=>200,
               'rec_data'=>$html,
               'closing_bl'=>number_format($ac_closing_balance,2,'.',','),
               'total_no_of_to_be_rec'=>$total_no_of_to_be_rec,

               'not_realized'=>$dr_not_realised_list,
               'not_realized_count'=>$dr_not_realised_count,
               'not_realized_total'=>number_format($dr_not_realised_total,2,'.',','),

               'not_presented'=>$cr_not_presented_list,
               'not_presented_count'=>$cr_not_presented_count,
               'not_presented_total'=>number_format($cr_not_presented_total,2,'.',','),

               'bank_balance'=>number_format($bank_balance,2,'.',','),
           ];
        }
        
        return null;

    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
       // return $request->all();
       try{
        $year = $request->year;
        $month = $request->month;

        $first_day_of_month = $year.'-'.$month.'-1';

        $endOfMonth = Carbon::createFromFormat('Y-m-d', $first_day_of_month)   
                        ->endOfMonth()   
                        ->format('Y-m-d');

        DB::beginTransaction();

        foreach($request->rows as $row){
            
            $update = DoubleEntryLedgerTransaction::find($row['ledger_transation_id']);
            $is_RecTemp =$update->is_reconcile;
            if(isset($update)){
                $update->is_reconcile = $row['is_reconcile'];
                $update->reconcile_at = $row['is_reconcile']==1 ? $endOfMonth : null;
                $update->save();
                
                if(($update->amount < 2) && ($is_RecTemp==0)){

                    if($row['is_reconcile']==1 || $row['is_update_amount']==1){
                        if(is_numeric($row['total_amount'])){

                            $updateTransationAmount = DoubleEntryTransaction::find($update->transaction_id);

                            if(isset($updateTransationAmount)){
                                $updateTransationAmount->total_amount = $row['total_amount'];
                                $updateTransationAmount->save();

                                $updateTransationAmount->ledgerTransactions()->where('entry_type','DR')->update(['amount'=>$row['total_amount']]);
                                $updateTransationAmount->ledgerTransactions()->where('entry_type','CR')->update(['amount'=>$row['total_amount']]);

                                $ledgerTransactions = $updateTransationAmount->ledgerTransactions()->select('account_id')->get();

                                foreach($ledgerTransactions as $transaction){
                                    $this->accountTransactionutility->updateBalanceOnTransaction($transaction->account_id);
                                }                        

                                $this->accountTransactionutility->updateUnAdjustedAmount($updateTransationAmount->id);
                            }  
                        }
                        
                        
                    }
            }
          }

        }

        DB::commit();

        return 'done';

       }catch(Exception $ex){
           DB::rollback();
           \Log::error($ex);
           return 'Error';
       }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('accounting::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('accounting::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
