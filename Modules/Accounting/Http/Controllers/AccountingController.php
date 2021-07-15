<?php

namespace Modules\Accounting\Http\Controllers;


use App\BusinessLocation;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables; 
use Modules\Accounting\Entities\Utility\Utility;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryAccountType;
use Modules\Accounting\Entities\Utility\AccountUtility;
use Modules\Accounting\Entities\DoubleEntryLedgerTransaction;
use Modules\Accounting\Entities\Utility\AccountTransactionUtility;

class AccountingController extends Controller
{
    
    protected $accountUtility;
    protected $utility;
    protected $businessUtil;

    public function __construct(Utility $utility, AccountUtility $accountUtility ,BusinessUtil $businessUtil) {
        
        
        $this->utility = $utility;
        $this->accountUtility = $accountUtility;
        $this->businessUtil = $businessUtil;
    }  

    

    public function index()
    {   
        if(!auth()->user()->can('accounting.access_double_entry_accounting')){
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        $date_filters['this_fy'] = $fy;
        $date_filters['this_month']['start'] = date('Y-m-01');
        $date_filters['this_month']['end'] = date('Y-m-t');
        $date_filters['this_week']['start'] = date('Y-m-d', strtotime('monday this week'));
        $date_filters['this_week']['end'] = date('Y-m-d', strtotime('sunday this week'));
        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        
        $bank_accounts = $this->utility->getBankAccountsWithBalance($business_id); 
        $default_in_hand_accounts = $this->utility->getInHandAccountsWithBalance($business_id); 

        

        return view('accounting::index',compact('all_locations','date_filters','bank_accounts','default_in_hand_accounts'));
    }

    public function getDashboardData(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $income = DoubleEntryAccountType::find(config('accounting.default_account_type.income'));
        $income->total_income = $income->getBalanceDateRange($start_date,$end_date);

        $expenses = DoubleEntryAccountType::find(config('accounting.default_account_type.expenses'));
        $expenses->total_expenses = $expenses->getBalanceDateRange($start_date,$end_date);
         
          return [
            'toal_income'=> number_format($income->total_income,2,'.',','),
            'total_expenses'=> number_format($expenses->total_expenses,2,'.',','),
            'total_profit'=> number_format(($income->total_income-$expenses->total_expenses),2,'.',','),
          ];  

    }

    public function masterData()
    {   
        $account_types = DoubleEntryAccountType::getDropDown();
        return view('accounting::master_data.index', compact('account_types'));
    }

    public function controlACIndex($control_ac_type, Request $request)
    {   
                 
        $business_id = $request->session()->get('user.business_id');  
        $contact_dropdown = [];

        

        if($control_ac_type == 'creditors'){

            $contact_dropdown = $this->utility->CreditorDropdown($business_id, false, false);
        }else{

            $contact_dropdown = $this->utility->DebitorDropdown($business_id, false, false);
        }
                 

        return view('accounting::control_accounts.index', compact('control_ac_type','contact_dropdown'));
    }
     
    public function getControlACData(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        if($request->ajax()){
            
            //join('double_entry_account_types as AT','AT.id','=','double_entry_account_categories.account_type_id')            
           $account_data = $this->accountUtility->getControlAccountTransaction($business_id, $request->account_type, $request->vendor_id, $request->start_date, $request->end_date);
                   
            
            return Datatables::of($account_data)

                            ->addColumn('desc', function ($row) {  
                                $html='';
                                $html .= $this->accountUtility->mapDocumentType($row->document_type,$row, $row->transactionDetails);  
                                if(isset($row->transactionPaymentDetails)){
                                    foreach($row->transactionPaymentDetails as $payment){
                                        $html .='<br> <span class="bg-green"> * | CHEQUE NO: '.$payment->transaction->cheque_no.' - Ref. :'.$payment->transaction->document_no.' - Amount. : '.number_format($payment->sub_amount,2,'.',',').'</span>';
                                    }
                                    
                                }                                         
                                return $html;
                            })
                            ->addColumn('debit', function ($row) {                                            
                                if($row->entry_type=='DR'){
                                    return number_format($row->amount,2,'.',',');
                                }else{
                                    return '';
                                }
                            })  
                            ->addColumn('credit', function ($row) {                                            
                                if($row->entry_type=='CR'){
                                    return number_format($row->amount,2,'.',',');
                                }else{
                                    return '';
                                }
                            }) 
                            ->editColumn('balance', function ($row) {                                            
                                return number_format($row->balance,2,'.',',');
                            }) 

                            ->rawColumns(['debit','credit','desc'])
                            ->make(true);

        }
    }

    public function getControlAccountSummary(Request $request)
    {   
        
        if($request->account_type=='debtor'){
            $account_id = config('accounting.default_account_ids.debtor');
        }else{
            $account_id =$account_id = config('accounting.default_account_ids.creditor'); 
        }

        $account = new AccountUtility();

        $account_summary_data = $account->getAccountSummary($account_id, $request->vendor_id);

        return [
            'opening_balance'=> number_format($account_summary_data->opening_balance,2,'.',','),
            'total_credit'=> number_format($account_summary_data->total_credit,2,'.',','),
            'total_debit'=> number_format($account_summary_data->total_debit,2,'.',','),
            'account_balance'=> number_format($account_summary_data->account_balance,2,'.',','),
        ];
    }

    public function getledgerSummary(Request $request)
    {   
        
        $account_id = $request->account_id;

        $account = new AccountUtility();

        $account_summary_data = $account->getAccountSummary($account_id);
        if(isset($account_summary_data)){
            return [
                'opening_balance'=> number_format($account_summary_data->opening_balance,2,'.',','),
                'total_credit'=> number_format($account_summary_data->total_credit,2,'.',','),
                'total_debit'=> number_format($account_summary_data->total_debit,2,'.',','),
                'account_balance'=> number_format($account_summary_data->account_balance,2,'.',','),
            ];
        }else
        {
            return [
                'opening_balance'=>0.00,
                'total_credit'=> 0.00,
                'total_debit'=> 0.00,
                'account_balance'=> 0.00,
            ];
        }
       
    }

    public function ledger(Request $request)
    {
        $business_id = $request->session()->get('user.business_id'); 
        $set_account_id =  isset($request->account_id) ? $request->account_id : null;         
        $accounts = DoubleEntryAccount::getLedgerAccountList();

        return view('accounting::accounts.ledger_account', compact('accounts','set_account_id'));
    }

    public function getledgerData(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        if($request->ajax()){
            
            //join('double_entry_account_types as AT','AT.id','=','double_entry_account_categories.account_type_id')             
            $account_data = $this->accountUtility->getLedgerTransaction($business_id, $request->account_id, null, $request->start_date, $request->end_date);
            
           // \Log::emergency(json_encode($account_data->get()));
            
            return Datatables::of($account_data)

                            ->addColumn('desc', function ($row) {  
                                $html='';
                                $html .= $this->accountUtility->mapDocumentType($row->document_type,$row, $row->transactionDetails);       
                                if(isset($row->transactionPaymentDetails)){
                                    foreach($row->transactionPaymentDetails as $payment){
                                        if($payment->transaction->is_canceled==1){
                                            $html .='<br> <span class="bg-red"> * | CHEQUE NO: '.$payment->transaction->cheque_no.' - Ref. :'.$payment->transaction->document_no.' - Amount. : '.number_format($payment->sub_amount,2,'.',',').'</span>';
                                        }else{
                                            $html .='<br> <span class="bg-green"> * | CHEQUE NO: '.$payment->transaction->cheque_no.' - Ref. :'.$payment->transaction->document_no.' - Amount. : '.number_format($payment->sub_amount,2,'.',',').'</span>';
                                        }
                                        
                                    }
                                    
                                }                                  
                                return $html;
                            })
                            ->addColumn('debit', function ($row) {                                            
                                if($row->entry_type=='DR'){
                                    return number_format($row->amount,2,'.',',');
                                }else{
                                    return '';
                                }
                            })  
                            ->addColumn('credit', function ($row) {                                            
                                if($row->entry_type=='CR'){
                                    return number_format($row->amount,2,'.',',');
                                }else{
                                    return '';
                                }
                            }) 
                            ->editColumn('balance', function ($row) {                                            
                                return number_format($row->balance,2,'.',',');
                            }) 

                            ->rawColumns(['debit','credit','desc'])
                            ->make(true);

        }
    }
}
