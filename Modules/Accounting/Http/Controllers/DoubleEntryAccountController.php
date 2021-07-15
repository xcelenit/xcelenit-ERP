<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryAccountType;
use Modules\Accounting\Entities\Utility\AccountUtility;
use Modules\Accounting\Entities\DoubleEntryAccountCategory;
use Modules\Accounting\Entities\Utility\AccountTransactionUtility;

class DoubleEntryAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $accountTras = new AccountTransactionUtility();

      // return $accountTras->updateUnAdjustedAmount(9);
        
        // $transaction=[
        //     'location_id'=>1,
        //     'contact_id'=>4,
        //     'transaction_date'=>'2020-12-25 18:39:00',
        //     'total_amount'=>2000,
        //     'invoice_no'=>'INV017',
        //     'total_cost'=>1000,
        //     'sys_transaction_id' =>null
        // ];
        // $payment=[];
        
        // $payment['location_id'] =1;
        // $payment['contact_id'] =4;
        // $payment['transaction_date'] ='2020-12-25 18:39:00';
        // $payment['total_amount'] =2000;
        // $payment['payment_method'] ='cash';
         

        // $payments[]= $payment;

        
        //  return $accountTras->createSalesEntry(1,$transaction, $payments);

        // $transaction=[
        //     'location_id'=>1,
        //     'contact_id'=>2,
        //     'transaction_date'=>'2020-12-26 18:39:00',
        //     'total_amount'=>5000,
        //     'purchase_ref'=>'GRN001',
        //     'supplier_invoice_no'=>'SINV0001',             
        // ];

        // return $accountTras->createPurchaseEntry(1,$transaction);

        // $reference_line=[];
        // $reference_line['ref_no'] = 'INV002';
        // $reference_line['desc'] = '';        
        // $reference_line['perent_transaction_id'] = 1;
        // $reference_line['sub_amount'] = 2500;

        // $reference_lines[]=$reference_line;

        // $payment['location_id'] =1;
        // $payment['contact_id'] =4;
        // $payment['transaction_date'] ='2020-12-25 18:39:00';
        // $payment['total_amount'] =2500;
        // $payment['payment_method'] ='cash';
        // $payment['payment_note'] = 'Partial Payment Receipt';
       
       // return $accountTras->createReceipt(1,$payment['location_id'],4, $payment,$reference_lines);

    //    $transaction=[
    //         'location_id'=>1,
    //         'contact_id'=>68,
    //         'category_id'=>2,
    //         'transaction_date'=>'2021-01-21 18:39:00',
    //         'total_amount'=>56153.95,
    //         'expenses_ref'=>'EP2020/0009',
    //         'supplier_invoice_no'=>'SINV00254', 
    //         'sys_transaction_id'=>6795,
    //         'expense_note'=>'ELECTRICITY BILL AT SHOP - NOVEMBER - 2020 UNIT-2447 (81373-78926) AC.NO 0302344603',
                        
    //     ];

    // return $accountTras->createExpenseEntry(1,$transaction);
               
        return view('accounting::accounts.index');
    }

    public function getData(Request $request)
    {   

        $business_id = $request->session()->get('user.business_id');

        if($request->ajax()){ 

                        
        
            $accounts_data =  DoubleEntryAccount::where('acc_accounts.business_id',$business_id)
                                                                ->with(['account_type','category'])
                                                                ->select('acc_accounts.*');
                                                                // ->orderBy('account_code','ASC');
            
            return Datatables::of($accounts_data)
                            ->addColumn('action',  function ($row) {
                                $html = '<div class="btn-group">
                                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                                data-toggle="dropdown" aria-expanded="false">' .
                                                __("messages.actions") .
                                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                                </span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-left" role="menu">' ;

                                $html .= '<li><a href="'.action("\Modules\Accounting\Http\Controllers\AccountingController@ledger").'?account_id='.$row->id.'"><i class="fas fa-eye" aria-hidden="true"></i> ' . 'View Ledger' . '</a></li>';
                                
                                $html .= '</ul></div>';

                                return $html;
                            })
                            ->addColumn('status', function ($row) {
                                  
                                $is_active='';
                                if(isset($row->is_active)){
                                    if($row->is_active==1){
                                        $is_active ='<span class="label bg-green">Active</span>';
                                        return $is_active;
                                    }else {
                                        $is_active ='<span class="label bg-red">Closed</span>';
                                        return $is_active;
                                    }
                                } 
                                
                            })  
                            ->editColumn('account_type', function ($row) {                                            
                                return $row->account_type->type;
                            })
                            ->editColumn('balance', function ($row) {                                            
                                return number_format($row->balance,2,'.',',');
                            })
                            ->editColumn('category_name', function ($row) {                                            
                                return $row->category->category_name;
                            })
                            // ->filterColumn('category_name', function ($query, $keyword) {
                            //     $query->whereRaw("CONCAT(double_entry_account_categories.category_code, ' - ', double_entry_account_categories.category_name, ' - ') like ?", ["%{$keyword}%"]);
                            // })                            
                            ->rawColumns(['action','status'])
                            ->make(true);

        }
        return null;
    }

    public function create()
    {   
        $account_types = DoubleEntryAccountType::getDropDown();
        $account_categories = DoubleEntryAccountCategory::getDropDown(1);
        $current_asset_category_id = config('accounting.default_categories.current_asset');
        
       // return $current_asset_category_id;
        return view('accounting::accounts.create',compact('account_types','account_categories','current_asset_category_id'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        // if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module')) || !auth()->user()->can('manufacturing.access_production')) {
        //     abort(403, 'Unauthorized action.');
        // }

        try{

            $request->validate([
                'account_type' => 'required',
                'account_category' => 'required',
                'account_name' => ['required','unique:acc_accounts,account_name'],
                'account_no' => ['required','unique:acc_accounts,account_no'],                
            ]);

            $is_bank = 0;

            if($request->account_category==config('accounting.default_categories.current_asset')){
                if(isset($request->is_bank)){
                    $is_bank = $request->is_bank;
                }                
            }

            $newAccount = new DoubleEntryAccount();
            
            $newAccount->business_id = $business_id;

            $newAccount->account_type_id = $request->account_type;
            $newAccount->category_id = $request->account_category;
            $newAccount->created_by = Auth::user()->id;
            $newAccount->account_code = DoubleEntryAccount::getNewAccountCode($request->account_category);
            $newAccount->account_name = $request->account_name;
            $newAccount->account_no = $request->account_no;
            $newAccount->balance = 0;
            $newAccount->is_active = 1;
            $newAccount->is_bank_ac = $is_bank;
            $newAccount->save();

            $output = ['success' => 1,
                'msg' => __('lang_v1.added_success')
            ];

        } catch(\Exception $e){
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => 0,
                                'msg' => __("messages.something_went_wrong")
                            ];
        }

        return redirect()->action('\Modules\Accounting\Http\Controllers\DoubleEntryAccountController@index')->with('status', $output);

    }

}
