<?php

namespace Modules\Accounting\Http\Controllers;

use App\User;
use App\Contact;
use App\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Utils\TransactionUtil;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Modules\Accounting\Entities\Utility\Utility;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryTransaction;
use Modules\Accounting\Entities\Utility\AccountUtility;
use Modules\Accounting\Entities\Utility\AccountTransactionUtility;

class DoubleEntryAccountTransactionController extends Controller
{
    protected $accountUtility;
    protected $utility;
    protected $accountTransactionutility;
    protected $transactionUtil;


    public function __construct(Utility $utility, AccountUtility $accountUtility,TransactionUtil $transactionUtil,AccountTransactionUtility $accountTransactionutility) {
        $this->utility = $utility;
        $this->accountUtility = $accountUtility;
        $this->transactionUtil = $transactionUtil;
        $this->accountTransactionutility = $accountTransactionutility;

    }  
    
    public function index()
     {  
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);
        $vendors = $this->utility->vendorDropdown($business_id, true);
        $document_types = $this->utility->getDocumentTypeDropDown();

        $users = $this->utility->getUserDropDown($business_id);
        //return $this->utility->getUserRoleName(Auth::user()->id);

        return view('accounting::transactions.index',compact('business_locations','vendors','document_types','users'));
     }

     public function getData(Request $request)
     {   
        
         $business_id = $request->session()->get('user.business_id');
 
         if($request->ajax()){
 
             
             $transaction_data =  DoubleEntryTransaction::where('acc_transactions.business_id',$business_id)
                                                                ->join('users as US','US.id','=','acc_transactions.added_by')
                                                                 ->with(['location','vendor','transactionDetails']);

                                                                 if ($request->has('ref_no')) {
                                                                    $ref_no = request()->get('ref_no');
                                                                    if (!empty($ref_no)) {
                                                                        $transaction_data->whereHas('transactionDetails', function($quary) use ($ref_no){
                                                                         return $quary->where('ref_no','LIKE','%'.$ref_no.'%');
                                                                        });
                                                                    }
                                                                  }

                                                                 //->whereHas(['transactionDetails'])
                                                                //  ->whereNotIn('document_type',['COS'])
                                                                $transaction_data->select('acc_transactions.*','US.username as added_user',
                                                                 DB::raw('(SELECT IF(acc_transactions.document_type="SAL",
                                                                            
                                                                          (SELECT SUM(ATD.sub_amount) FROM acc_transactions AS RC 
                                                                            INNER JOIN acc_transaction_details AS ATD 
                                                                            ON RC.id = ATD.transaction_id 
                                                                            WHERE RC.document_type IN ("RCP","JNV","BDA") AND RC.is_canceled="0" AND ATD.perent_transaction_id = acc_transactions.id),
                                                                            
                                                                            
                                                                            (SELECT SUM(ATD.sub_amount) FROM acc_transactions AS RC 
                                                                            INNER JOIN acc_transaction_details AS ATD 
                                                                            ON RC.id = ATD.transaction_id 
                                                                            WHERE RC.document_type IN ("CPV","PCV","JNV","BCA") AND RC.is_canceled="0" AND ATD.perent_transaction_id = acc_transactions.id) ) ) AS total_paid')
                                                                            );


            

            if ($request->has('location_id')) {
               $location_id = request()->get('location_id');
               if (!empty($location_id)) {
                   $transaction_data->where('location_id', $location_id);
               }
             }

             if ($request->has('document_type')) {
                $document_type = request()->get('document_type');
                if (!empty($document_type)) {
                    $transaction_data->where('document_type', $document_type);
                }
              }

             if ($request->has('vendor_id')) {
                $vendor_id = request()->get('vendor_id');
                if (!empty($vendor_id)) {
                    $transaction_data->where('vendor_id', $vendor_id);
                }
              }

              if ($request->has('user_id')) {
                $user_id = request()->get('user_id');
                if (!empty($user_id)) {
                    $transaction_data->where('added_by', $user_id);
                }
              }

              if ($request->has('payment_status')) {
                $payment_status = request()->get('payment_status');
                if (!empty($payment_status)) {
                    if($payment_status=='unpaid'){
                        $transaction_data->whereIn('payment_status', ['due','partial']);
                    }else{
                        $transaction_data->where('payment_status', $payment_status);
                    }
                    
                }
              }
                           

              if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $transaction_data->whereDate('transaction_date', '>=', $start)
                            ->whereDate('transaction_date', '<=', $end);
              }
             
              
             return Datatables::of($transaction_data)
                             ->addColumn('action',  function ($row) {
                                 $role_name = $this->utility->getUserRoleName(Auth::user()->id);
                                $html = '<div class="btn-group">
                                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                                data-toggle="dropdown" aria-expanded="false">' .
                                                __("messages.actions") .
                                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                                </span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-left" role="menu">' ;
        
                                $html .= '<li><a href="#"  class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . 'View' . '</a></li>';
                                
                                if($row->document_type==config('accounting.document_type_prefix.cheque_payment_vc')){
                                    $html .= '<li><a target="_blank" href="'.action("\Modules\Accounting\Http\Controllers\DoubleEntryAccountDocumentController@printPaymentVoucher", [$row->id]).'" 
                                          class="btn-modsal"><i class="fas fa-print" aria-hidden="true"></i> ' . 'Print Voucher' . '</a></li>';
                                    if($row->is_print_chq==0){
                                        $html .= '<li><a class="cheque-print" target="_blank" href="'.action("\Modules\Accounting\Http\Controllers\DoubleEntryAccountDocumentController@printCheque", [$row->id]).'" 
                                          class="btn-modals" ><i class="fas fa-money-bill-alt" aria-hidden="true"></i> ' . 'Print Cheque' . '</a></li>';
                                    }else{
                                        if(($role_name =='Admin') || ($role_name =='Administrator'))   {
                                            $html .= '<li><a class="cheque-print" target="_blank" href="'.action("\Modules\Accounting\Http\Controllers\DoubleEntryAccountDocumentController@printCheque", [$row->id]).'" 
                                            class="btn-modasl" ><i class="fas fa-undo" aria-hidden="true"></i> ' . 'Restore Cheque Print' . '</a></li>';
                                            
                                        }
                                    }

                                    $html .= '<li><a href="/accounting/payment/cheque-payment-voucher/'.$row->id.'/edit" ><i class="fas fa-eye" aria-hidden="true"></i> ' . 'Edit' . '</a></li>';
                                }

                                if(($role_name =='Admin') || ($role_name =='Administrator'))   {  
                                    if(in_array($row->document_type,config('accounting.editable_doc_type_array'))){
                                         
                                        if($row->document_type=='RCP'){
                                            $html .= '<li><a href="/accounting/payment/receipt/'.$row->id.'/edit"  class=""><i class="fas fa-eye" aria-hidden="true"></i> ' . 'Edit' . '</a></li>';
                                         }
                                        
                                    }
                                }
                                  
        
                                $html .= '</ul></div>';
        
                                return $html;
                            })
                            ->editColumn(
                                'payment_note',
                                function ($row) {
                                     $desc='';
                                     if(isset($row->vendor)){
                                        if($row->vendor->type=='supplier' || $row->vendor->type=='both'){
                                            $desc=$row->vendor->supplier_business_name;
                                        }else{
                                            $desc=$row->vendor->name; 
                                        }

                                        return $desc.=' - '.$this->accountUtility->mapDocumentType($row->document_type,$row,$row->transactionDetails); 
                                     }

                                     return $this->accountUtility->mapDocumentType($row->document_type,$row,$row->transactionDetails);                                    
                                }
                            )
                             ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                             ->editColumn(
                                'payment_status_info',
                                function ($row) {
                                    $payment_status='';

                                    if(isset($row->payment_status)){
                                        if($row->payment_status=='due'){
                                            $payment_status ='<span class="label bg-yellow">DUE</span>';
                                            return $payment_status;
                                        }else if($row->payment_status=='patial'){
                                            $payment_status ='<span class="label bg-light-blue">PARTIAL</span>';
                                            return $payment_status;
                                        }else{
                                            $payment_status ='<span class="label bg-green">PAID</span>';
                                            return $payment_status;
                                        }
                                    }                                   

                                    return null;

                                    
                                }
                            )
                            ->editColumn(
                                'total_amount',
                                function ($row) {
                                    // return number_format($row->total_amount,2,'.',',');
                                     return '<span class="display_currency row_total"  data-orig-value="' . $row->total_amount . '">' . number_format($row->total_amount,2,'.',',') . '</span>';
                                }
                            )->editColumn(
                                'total_paid',
                                function ($row) {
                                     return (isset($row->total_paid)) ? number_format($row->total_paid,2,'.',',') : '-';
                                }
                            )
                            ->editColumn(
                                'total_unaj_amount',
                                function ($row) {
                                     return number_format($row->total_unaj_amount,2,'.',',');
                                }
                            )
                             ->addColumn('status', function ($row) {  
                                $is_canceled='';
                                if(isset($row->is_canceled)){
                                    if($row->is_canceled==0){
                                        if($row->status == 1){
                                            $is_canceled ='<span class="label bg-info">NEW</span>';
                                        }else if($row->status == 2){
                                            $is_canceled ='<span class="label bg-green">Deposited</span>';
                                        }        

                                        return $is_canceled;
                                    }else {
                                        $is_canceled ='<span class="label bg-danger">CANCELED</span>';
                                        return $is_canceled;
                                    }
                                } 
                                 
                             })
                             ->rawColumns(['action','status','payment_status_info','payment_note','cheque_no','total_amount'])
                             ->make(true);
 
         }
         return null;
     }
}
