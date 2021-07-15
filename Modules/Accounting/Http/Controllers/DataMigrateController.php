<?php

namespace Modules\Accounting\Http\Controllers;

use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Utils\TransactionUtil;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\Utility\Utility;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\Utility\AccountUtility;
use Modules\Accounting\Entities\DoubleEntryLedgerTransaction;
use Modules\Accounting\Entities\Utility\AccountTransactionUtility;

class DataMigrateController extends Controller
{   

    protected $accountUtility;
    protected $utility;
    protected $accountTransactionutility;
    protected $transactionUtil;
    protected $date_range;


    public function __construct(Utility $utility, AccountUtility $accountUtility,TransactionUtil $transactionUtil,AccountTransactionUtility $accountTransactionutility) {
        $this->utility = $utility;
        $this->accountUtility = $accountUtility;
        $this->transactionUtil = $transactionUtil;
        $this->accountTransactionutility = $accountTransactionutility;
        $this->date_range = ['2021-01-12','2021-01-27'];
    }  

    public function selesMigrate()
    {   

        if(!auth()->user()->can('accounting.access_double_entry_accounting')){
            abort(403, 'Unauthorized action.');
        }
        
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2000M');
        
        $business_id = request()->session()->get('user.business_id');  

        $Transactions = Transaction::whereBetween(DB::raw('date(transactions.transaction_date)'),$this->date_range)
                                    ->leftJoin('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')
                                    ->leftjoin('transaction_sell_lines_purchase_lines as TSPL', 'tsl.id', '=', 'TSPL.sell_line_id')                                    
                                    ->leftjoin(
                                        'purchase_lines as PL',
                                        'TSPL.purchase_line_id',
                                        '=',
                                        'PL.id'
                                    )
                                    ->leftJoin('products as P', 'tsl.product_id', '=', 'P.id')
                                    ->leftJoin(
                                        'transactions AS SR',
                                        'transactions.id',
                                        '=',
                                        'SR.return_parent_id'
                                    )                                     
                                    ->leftJoin('transaction_sell_lines as tsl_rt', 'transactions.return_parent_id', '=', 'tsl_rt.transaction_id')
                                    ->leftjoin('transaction_sell_lines_purchase_lines as TSPL_rt', 'tsl_rt.id', '=', 'TSPL_rt.sell_line_id')  
                                    ->leftjoin(
                                        'purchase_lines as PL_rt',
                                        'TSPL_rt.purchase_line_id',
                                        '=',
                                        'PL_rt.id'
                                    )
                                    ->leftJoin('products as P_rt', 'tsl_rt.product_id', '=', 'P_rt.id')
                                    ->with('payment_lines')                                  
                                    ->whereIn('transactions.type',['sell','sell_return'])
                                    // ->whereIn('transactions.id',[318, 507,509,510])
                                    ->where('transactions.is_quotation',0)
                                    ->where('transactions.status','final')
                                    ->where('transactions.business_id',$business_id)
                                    ->select('transactions.id',
                                              'transactions.location_id',
                                              'transactions.payment_status',
                                              'transactions.contact_id',
                                              'transactions.invoice_no',
                                              'transactions.ref_no',
                                              'transactions.transaction_date',
                                              'transactions.final_total',
                                              'transactions.return_parent_id',
                                              'transactions.type',
                                            //   'transactions.ref_no',

                                              

                                        DB::raw('SUM(IF (TSPL.id IS NULL AND P.type="combo", ( 
                                            SELECT Sum((tspl2.quantity - tspl2.qty_returned) * (pl2.purchase_price_inc_tax)) AS total
                                                FROM transaction_sell_lines AS tsln
                                                    JOIN transaction_sell_lines_purchase_lines AS tspl2
                                                ON tsln.id=tspl2.sell_line_id 
                                                JOIN purchase_lines AS pl2 
                                                ON tspl2.purchase_line_id = pl2.id 
                                                WHERE tsln.parent_sell_line_id = tsl.id), 
                                            ( SELECT IF(PL.purchase_price_inc_tax IS NOT NULL, 
                                                        ( TSPL.quantity - TSPL.qty_returned) * (PL.purchase_price_inc_tax), 
                                                        
                                                    ( SELECT IF( TSPL.id IS NULL AND P.enable_stock=1, 
                                                                    ( ( TSPL.quantity - TSPL.qty_returned) * ((SELECT MAX(PV.dpp_inc_tax) FROM variations AS PV WHERE PV.product_id = P.id) ) ),
                                                                    ( ( tsl.quantity - tsl.quantity_returned) * ((SELECT MAX(PV.dpp_inc_tax) FROM variations AS PV WHERE PV.product_id = P.id) ) )  )) 
                                                        
                                                        ))
                                                )) AS sale_cost'),

                                        
                                        DB::raw('SUM(IF (TSPL_rt.id IS NULL AND P_rt.type="combo", ( 

                                            SELECT Sum((tspl2_rt.qty_returned) * (pl2_rt.purchase_price_inc_tax)) AS total
                                                FROM transaction_sell_lines AS tsln_rt
                                                    JOIN transaction_sell_lines_purchase_lines AS tspl2_rt
                                                ON tsln_rt.id=tspl2_rt.sell_line_id 
                                                JOIN purchase_lines AS pl2_rt 
                                                ON tspl2_rt.purchase_line_id = pl2_rt.id 
                                                WHERE tsln_rt.parent_sell_line_id = tsl_rt.id), 

                                            ( SELECT IF(PL_rt.purchase_price_inc_tax IS NOT NULL, 
                                                        ( TSPL_rt.qty_returned) * (PL_rt.purchase_price_inc_tax), 
                                                        
                                                    ( SELECT IF( TSPL_rt.id IS NULL AND P_rt.enable_stock=1, 
                                                                    ( ( TSPL_rt.qty_returned) * ((SELECT MAX(PV_rt.dpp_inc_tax) FROM variations AS PV_rt WHERE PV_rt.product_id = P_rt.id) ) ),
                                                                    ( ( tsl_rt.quantity_returned) * ((SELECT MAX(PV_rt.dpp_inc_tax) FROM variations AS PV_rt WHERE PV_rt.product_id = P_rt.id) ) )  )) 
                                                        
                                                        ))
                                                )) AS return_cost'),

                                        DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                                                TP.transaction_id=transactions.id) as total_paid'), 

                                        DB::raw('COUNT(SR.id) as return_exists'),
                                        DB::raw('(SELECT SUM(TP2.amount) FROM transaction_payments AS TP2 WHERE
                                                TP2.transaction_id=SR.id ) as return_paid'),

                                        DB::raw('COALESCE(SR.final_total, 0) as amount_return')
                                                
                                    )->orderBy('transactions.id')
                                    ->groupBy('transactions.id')
                                    ->get();
      //  return $Transactions;

        foreach($Transactions as $trans){

             $transaction=[
                    'location_id'=>$trans->location_id,
                    'contact_id'=>$trans->contact_id,
                    'transaction_date'=>$trans->transaction_date,
                    'total_amount'=>$trans->final_total,
                    'invoice_no'=>$trans->invoice_no,
                    'total_cost'=> ($trans->type=='sell') ? ($trans->sale_cost) : ($trans->return_cost),
                    'sys_transaction_id'=>$trans->id
                ];


                $payments=[];

                foreach($trans->payment_lines as $payment_line){
                    //return  $payment_line;

                    $payment['location_id'] =$trans->location_id;
                    $payment['contact_id'] = $trans->contact_id;
                    $payment['transaction_date'] =$payment_line->paid_on;
                    $payment['total_amount'] =$payment_line->amount;
                    $payment['payment_method'] =$payment_line->method;
                    
                    $payments[]= $payment;

                }
                                
               if($trans->type == 'sell'){
                   $this->accountTransactionutility->createSalesEntry($business_id,$transaction, $payments);
               }else{
                   // Sell return
                   $this->accountTransactionutility->createCreditNote($business_id, $transaction, $payments);
               }

        }

        return 'DONE';
    }


    public function purchasesMigrate()
    {   
        if(!auth()->user()->can('accounting.access_double_entry_accounting')){
            abort(403, 'Unauthorized action.');
        }
        
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2000M');
        
        $business_id = request()->session()->get('user.business_id');  

        $Transactions = Transaction::whereBetween(DB::raw('date(transactions.transaction_date)'),$this->date_range)                                                                     
                                    ->with('payment_lines')                                  
                                    ->whereIn('transactions.type',['purchase','purchase_return'])
                                    // ->whereIn('transactions.id',[3965, 3966, 3967])
                                    ->where('transactions.is_quotation',0)
                                    ->whereIn('transactions.status', ['received','final'])
                                    ->where('transactions.business_id',$business_id)
                                    ->select('transactions.id',
                                              'transactions.location_id',
                                              'transactions.payment_status',
                                              'transactions.contact_id',
                                              'transactions.invoice_no',
                                              'transactions.ref_no',
                                              'transactions.transaction_date',
                                              'transactions.final_total',
                                              'transactions.return_parent_id',
                                              'transactions.type'
                                            //   'transactions.ref_no',                                              
                                                
                                    )->orderBy('transactions.id')
                                    ->groupBy('transactions.id')
                                    ->get();
       // return $Transactions;

        foreach($Transactions as $trans){

             $transaction=[
                    'location_id'=>$trans->location_id,
                    'contact_id'=>$trans->contact_id,
                    'transaction_date'=>$trans->transaction_date,
                    'total_amount'=>$trans->final_total,
                    'purchase_ref'=>$trans->invoice_no,
                    // 'total_cost'=> ($trans->final_total - $trans->gross_profit),
                    'sys_transaction_id'=>$trans->id
                ];

                     
                
                

                $payments=[];

                foreach($trans->payment_lines as $payment_line){
                    //return  $payment_line;

                    $payment['location_id'] =$trans->location_id;
                    $payment['contact_id'] = $trans->contact_id;
                    $payment['transaction_date'] =$payment_line->paid_on;
                    $payment['total_amount'] =$payment_line->amount;
                    $payment['payment_method'] =$payment_line->method;
                    
                    $payments[]= $payment;

                }
                
               //return $transaction;
                if($trans->type == 'purchase'){
                    $purchase_entry = $this->accountTransactionutility->createPurchaseEntry($business_id,$transaction);

                    

                    if(isset($payments)){
                        foreach($payments as $payment_row){

                            $reference_line=[];
                            $reference_line['ref_no'] = $trans->invoice_no;
                            $reference_line['desc'] = null;
                            $reference_line['perent_transaction_id'] = $purchase_entry->id;
                            $reference_line['sub_amount'] = $payment_row['total_amount']; 

                            $reference_lines[]= $reference_line;

                            $this->accountTransactionutility->createCreditorVoucher($business_id, 
                                    $payment_row['location_id'],
                                    $payment_row['contact_id'],
                                    $payment_row,
                                    $reference_lines,
                                    $purchase_entry->id); 
                        }                       
                    }

                }else{
                    // Sell return
                    $this->accountTransactionutility->createDebitNote($business_id, $transaction, $payments);
                }
                

        }

        return 'DONE!';
    }


    public function expensesMigrate()
    {   
        if(!auth()->user()->can('accounting.access_double_entry_accounting')){
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2000M');
        
        $business_id = request()->session()->get('user.business_id');  

        $Transactions = Transaction::whereBetween(DB::raw('date(transactions.transaction_date)'),['2020-12-01','2021-01-11'])                                                                     
                                    ->with('payment_lines')                                  
                                    ->whereIn('transactions.type',['expense'])
                                    // ->whereIn('transactions.id',[3965, 3966, 3967])
                                    ->where('transactions.is_quotation',0)
                                    ->whereNotNull('transactions.contact_id')
                                    ->whereIn('transactions.status', ['final'])
                                    ->where('transactions.business_id',$business_id)
                                    ->select('transactions.id',
                                              'transactions.location_id',
                                              'transactions.payment_status',
                                              'transactions.contact_id',
                                              'transactions.invoice_no',
                                              'transactions.ref_no',
                                              'transactions.transaction_date',
                                              'transactions.final_total',
                                              'transactions.return_parent_id',
                                              'transactions.type'
                                            //   'transactions.ref_no',                                              
                                                
                                    )->orderBy('transactions.id')
                                    ->groupBy('transactions.id')
                                    ->get();
        return $Transactions;

        foreach($Transactions as $trans){

             $transaction=[
                    'location_id'=>$trans->location_id,
                    'contact_id'=>$trans->contact_id,
                    'transaction_date'=>$trans->transaction_date,
                    'total_amount'=>$trans->final_total,
                    'purchase_ref'=>$trans->invoice_no,
                    // 'total_cost'=> ($trans->final_total - $trans->gross_profit),
                    'sys_transaction_id'=>$trans->id
                ];

                     
                
                

                $payments=[];

                foreach($trans->payment_lines as $payment_line){
                    //return  $payment_line;

                    $payment['location_id'] =$trans->location_id;
                    $payment['contact_id'] = $trans->contact_id;
                    $payment['transaction_date'] =$payment_line->paid_on;
                    $payment['total_amount'] =$payment_line->amount;
                    $payment['payment_method'] =$payment_line->method;
                    
                    $payments[]= $payment;

                }
                
               //return $transaction;
                if($trans->type == 'purchase'){
                    $purchase_entry = $this->accountTransactionutility->createPurchaseEntry($business_id,$transaction);

                    

                    if(isset($payments)){
                        foreach($payments as $payment_row){

                            $reference_line=[];
                            $reference_line['ref_no'] = $trans->invoice_no;
                            $reference_line['desc'] = null;
                            $reference_line['perent_transaction_id'] = $purchase_entry->id;
                            $reference_line['sub_amount'] = $payment_row['total_amount']; 

                            $reference_lines[]= $reference_line;

                            $this->accountTransactionutility->createCreditorVoucher($business_id, 
                                    $payment_row['location_id'],
                                    $payment_row['contact_id'],
                                    $payment_row,
                                    $reference_lines,
                                    $purchase_entry->id); 
                        }                       
                    }

                }else{
                    // Sell return
                    $this->accountTransactionutility->createDebitNote($business_id, $transaction, $payments);
                }
                

        }

        return 'DONE!';
    }
    

    public function test()
    {
         if(!auth()->user()->can('accounting.access_double_entry_accounting')){
            abort(403, 'Unauthorized action.');
        }
        return $this->accountUtility->getLedgerTransaction(1, 4 , null, '2020-11-30', '2020-12-10');
        
        $type = 'debtor';
        $contact_id = 1;
        $start_date = '2021-01-06';
        $end_date = '2021-01-06';

        if($type == 'debtor'){
          $account_id = config('accounting.default_account_ids.debtor');
        }else{
          $account_id = config('accounting.default_account_ids.creditor'); 
        }

        $account = DoubleEntryAccount::find($account_id);
           

        $openning_bl = $account->getBalance($contact_id,$start_date);
        
        $account_data = DoubleEntryLedgerTransaction::join('acc_accounts AS A','A.id','=','acc_ledger_transactions.account_id')
        ->join('acc_transactions as AT','AT.id','=','acc_ledger_transactions.transaction_id')
        ->with(['transactionDetails'])
        ->where('A.id',$account_id)
        ->where('AT.business_id',1);

        if(isset($contact_id)){
            $account_data->where('AT.vendor_id',$contact_id);
        }                      

      $data =   $account_data->where('AT.is_canceled',0)
        ->whereBetween(DB::raw('date(AT.transaction_date)'), [$start_date, $end_date])
        ->select(['acc_ledger_transactions.*','AT.added_by','AT.vendor_id','AT.document_no','AT.document_type','AT.transaction_date','AT.payment_method','A.account_type_id as ac_type',
                //   'AT.total_amount as balance',
                DB::raw("(SELECT @b := (IF(acc_ledger_transactions.entry_type=IF((ac_type <= 2), 'DR', 'CR'), @b + $openning_bl + acc_ledger_transactions.amount, @b + $openning_bl - acc_ledger_transactions.amount)) as balance FROM (SELECT @b := 0.0) as dummy ) balance")
                    
                    
          //   DB::raw('(SELECT SUM(IF(ALE.entry_type=IF((ac_type <= 2), "DR", "CR"), ALE.amount, -1 * ALE.amount)) FROM acc_ledger_transactions AS ALE 
          //           INNER JOIN acc_transactions AS ATS ON ALE.transaction_id = ATS.id WHERE ATS.transaction_date <= AT.transaction_date AND ALE.account_id = acc_ledger_transactions.account_id 
          //           AND ATS.is_canceled=0 AND ALE.id <= acc_ledger_transactions.id) as balance')
            ])                        //IF(A.account_type_id >= 3,"CR","DR")
        ->groupBy('acc_ledger_transactions.id')                    
        ->orderBy('AT.transaction_date', 'ASC')
        ->orderBy('acc_ledger_transactions.id', 'ASC')
        ->get();

        return $data;
    }
}
