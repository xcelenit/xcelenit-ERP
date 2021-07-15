<?php

namespace Modules\Accounting\Http\Controllers;

use Exception;
use App\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Utils\TransactionUtil;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\Utility\Utility;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryTransaction;
use Modules\Accounting\Entities\Utility\AccountUtility;
use Modules\Accounting\Entities\Utility\AccountTransactionUtility;

class DoubleEntryAccountPaymentController extends Controller
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
    
    public function addReceipt()
    {   
        $business_id = request()->session()->get('user.business_id');  
        $doc_type_code = config('accounting.document_type_prefix.receipt');
        $doc_type = $this->accountUtility->getDocumentTypeByCode($doc_type_code);
        $contact_dropdown = $this->utility->DebitorDropdown($business_id, false, false);
         
        $payment_method_array = collect(config('accounting.payment_methods'));
        $payment_method_array = $payment_method_array->where('method','!=','none');
        // $payment_method_array=[];
        // $payment_method_array = $payment_methods->pluck('name','default_account_id');

        $debit_account_list = DoubleEntryAccount::getReceiptDebitAccountList();

        $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
        $locations = $locations->prepend('None', '');

        //return $debit_account_list;

        $current_fy_period = $this->accountTransactionutility->getCurrentFinancialYear($business_id, date('Y-m-d'));
        
        return view('accounting::payments.add_receipt', compact('doc_type','contact_dropdown','payment_method_array','current_fy_period','debit_account_list','locations'));
    }

    public function editReceipt($transaction_id)
    {   
        $business_id = request()->session()->get('user.business_id');
        $doc_type_code = config('accounting.document_type_prefix.receipt');    
        $transaction = DoubleEntryTransaction::where('id',$transaction_id)
                        ->where('document_type',$doc_type_code)
                        ->where('is_canceled',0)
                        ->where('status',1)
                        ->with(['ledgerTransactions','transactionDetails'])
                        ->select('acc_transactions.*', DB::raw('(SELECT account_id FROM acc_ledger_transactions WHERE transaction_id = acc_transactions.id AND entry_type="DR") AS debit_account_id'))
                        ->first();
          
        //return $transaction;
                        
        if(isset($transaction)){
            $doc_type = $this->accountUtility->getDocumentTypeByCode($doc_type_code);
            $contact_dropdown = $this->utility->DebitorDropdown($business_id, false, false);
             
            $payment_method_array = collect(config('accounting.payment_methods'));
            $payment_method_array = $payment_method_array->where('method','!=','none');
             
            // $payment_method_array=[];
            // $payment_method_array = $payment_methods->pluck('name','default_account_id');
    
            $debit_account_list = DoubleEntryAccount::getReceiptDebitAccountList();
    
            $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
            $locations = $locations->prepend('None', '');
    
            //return $debit_account_list;
    
            $current_fy_period = $this->accountTransactionutility->getCurrentFinancialYear($business_id, date('Y-m-d'));
            
            return view('accounting::payments.add_receipt', 
                    compact('transaction','doc_type','contact_dropdown','payment_method_array','current_fy_period','debit_account_list','locations'));
        }else{
            return abort(404);
        }
           
       
    }

    public function addPettyCashVoucher()
    {   
        $business_id = request()->session()->get('user.business_id');  
        $doc_type_code = config('accounting.document_type_prefix.petty_cash_vc');
        $doc_type = $this->accountUtility->getDocumentTypeByCode($doc_type_code);
        $contact_dropdown = $this->utility->CreditorDropdown($business_id, false, false);
         
        $payment_method_array = collect(config('accounting.payment_methods'));
        $payment_method_array = $payment_method_array->where('method','cash');

        $debit_account_list = DoubleEntryAccount::getVoucherDebitAccountList();     
        $credit_account_list = DoubleEntryAccount::getPettyCashAccountList();

        $creditor_account_id = config('accounting.default_account_ids.creditor');

        $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
        $locations = $locations->prepend('None', '');

        
        $current_fy_period = $this->accountTransactionutility->getCurrentFinancialYear($business_id, date('Y-m-d'));
        
        return view('accounting::payments.add_payment_voucher', compact('doc_type','contact_dropdown','creditor_account_id','payment_method_array','current_fy_period','debit_account_list','credit_account_list','locations'));
    }

    public function addChequeVoucher()
    {
        $business_id = request()->session()->get('user.business_id');  
        $doc_type_code = config('accounting.document_type_prefix.cheque_payment_vc');
        $doc_type = $this->accountUtility->getDocumentTypeByCode($doc_type_code);
        $contact_dropdown = $this->utility->CreditorDropdown($business_id, false, false);
         
        $payment_method_array = collect(config('accounting.payment_methods'));
        $payment_method_array = $payment_method_array->where('method','cheque')->where('method','!=','none');

        $debit_account_list = DoubleEntryAccount::getVoucherDebitAccountList();   
        $credit_account_list = DoubleEntryAccount::getBankAccountList();

        $creditor_account_id = config('accounting.default_account_ids.creditor');

        $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
        $locations = $locations->prepend('None', '');

        $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
        $locations = $locations->prepend('None', '');

               
        $current_fy_period = $this->accountTransactionutility->getCurrentFinancialYear($business_id, date('Y-m-d'));
        
        return view('accounting::payments.add_payment_voucher', compact('doc_type','contact_dropdown','debit_account_list','creditor_account_id','payment_method_array','current_fy_period','credit_account_list','locations'));
    }

    public function editChequeVoucher($transaction_id)
    {
        $business_id = request()->session()->get('user.business_id');  
        $doc_type_code = config('accounting.document_type_prefix.cheque_payment_vc');

        $transaction = DoubleEntryTransaction::where('id',$transaction_id)
                        ->where('document_type',$doc_type_code)
                        ->where('is_canceled',0)
                        ->where('status',1)
                        ->with(['ledgerTransactions','transactionDetails'])
                        ->select('acc_transactions.*', 
                            DB::raw('(SELECT account_id FROM acc_ledger_transactions WHERE transaction_id = acc_transactions.id AND entry_type="DR") AS debit_account_id'),
                            DB::raw('(SELECT account_id FROM acc_ledger_transactions WHERE transaction_id = acc_transactions.id AND entry_type="CR") AS credit_account_id'))
                        ->first();
        if(isset($transaction)){
            $doc_type = $this->accountUtility->getDocumentTypeByCode($doc_type_code);
            $contact_dropdown = $this->utility->CreditorDropdown($business_id, false, false);
             
            $payment_method_array = collect(config('accounting.payment_methods'));
            $payment_method_array = $payment_method_array->where('method','cheque')->where('method','!=','none');
    
            $debit_account_list = DoubleEntryAccount::getVoucherDebitAccountList();   
            $credit_account_list = DoubleEntryAccount::getBankAccountList();
    
            $creditor_account_id = config('accounting.default_account_ids.creditor');
    
            $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
            $locations = $locations->prepend('None', '');
    
            $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
            $locations = $locations->prepend('None', '');
    
                   
            $current_fy_period = $this->accountTransactionutility->getCurrentFinancialYear($business_id, date('Y-m-d'));
            
            return view('accounting::payments.add_payment_voucher', 
            compact('doc_type','contact_dropdown','debit_account_list',
                    'creditor_account_id','payment_method_array','current_fy_period','credit_account_list','locations','transaction'));
            
        }else{
            return abort(404);
        }
       
    }

    public function addJournalEntry()
    {    
        $business_id = request()->session()->get('user.business_id');  
        $doc_type_code = config('accounting.document_type_prefix.journal_vo');
        $doc_type = $this->accountUtility->getDocumentTypeByCode($doc_type_code);
        $contact_dropdown = $this->utility->vendorDropdown($business_id, false, false);
        $contact_dropdown =  $contact_dropdown->prepend('None', '');
         
        $payment_method_array = collect(config('accounting.payment_methods'));
        $payment_method_array = $payment_method_array->where('method','!=','card');
        

        $debit_account_list = DoubleEntryAccount::getLedgerAccountListAll();                                                                     
        $credit_account_list = DoubleEntryAccount::getLedgerAccountListAll();


        $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
        $locations = $locations->prepend('None', '');

        // $locations = BusinessLocation::where('business_id',$business_id)->pluck('name','id');
        // $locations = $locations->prepend('None', '');

        $debtor_account_id = config('accounting.default_account_ids.debtor');
        $creditor_account_id = config('accounting.default_account_ids.creditor');

               
        $current_fy_period = $this->accountTransactionutility->getCurrentFinancialYear($business_id, date('Y-m-d'));
        
        return view('accounting::payments.add_journal_entry', compact('doc_type','debtor_account_id','creditor_account_id',
        'contact_dropdown','payment_method_array','current_fy_period','debit_account_list','credit_account_list','locations'));
    }

    public function storeJornalVoucher(Request $request)
    {   
       // return $request->all();
       try{

        DB::beginTransaction();
                $business_id = $request->session()->get('user.business_id');       
                $reference_lines= [];
                $details_list =  $request->details_list;

                $is_opening_bl = $request->is_opening;

                if(isset($details_list)>0){
                    foreach($details_list as $item){
                        
                        $reference_lines[]= [
                                        'perent_transaction_id'=>$item['perent_transaction_id'],
                                        'ref_no'=>$item['ref_no'],
                                        'sub_amount'=>$item['sub_amount'],
                                        'desc'=>isset($item['desc']) ? $item['desc'] : ''
                                    ];
                    }
                }

                $payment = [
                    'location_id' =>isset($request->location_id) ? $request->location_id : null,
                    'contact_id' => $request->vendor_id,
                    'transaction_date' => date("Y-m-d H:i:s",strtotime($request->transaction_date)),
                    'payment_method' => strtolower($request->payment_method),
                    'payment_note' => $request->payment_note,
                    'cheque_date' => isset($request->cheque_date) ? date("Y-m-d", strtotime($request->cheque_date)) : null,
                    'cheque_no' => $request->cheque_no,
                    'total_amount' => $request->amount,
                    'credit_account_id' =>$request->credit_account_id,
                    'debit_account_id' =>$request->debit_account_id,
                    'document_type' => config('accounting.document_type_prefix.journal_vo'),
                    'payee' => ($request->payment_method=='CHEQUE') ? (isset($request->payee) ? $request->payee : null) : null,
                ];

            
            $transaction =  $this->accountTransactionutility->createJornalVoucher($business_id, $payment['location_id'], $request->vendor_id, $payment,$reference_lines, $is_opening_bl);
        DB::commit();
        return 'done';

       }catch(Exception $ex){
           \Log::error($ex);
           DB::rollback();
           return 'error';
       }
       
    }

    public function getPyeeByVendor(Request $request)
    {
        $payees = $this->utility->CreditorPayeeDropdown($request->vendor_id);
        $htlm='';
        if(isset($payees)){
            $htlm='<option value="">None</option>';
            foreach($payees as $payee){
                $htlm.='<option value="'.$payee.'">'.$payee.'</option>';
            }
        }
        return $htlm;
    }

    public function getDebtorDueInvoice(Request $request)
    {   
        $business_id = $request->session()->get('user.business_id');
        
        $payment_transaction_id = isset($request->transaction_id) ? $request->transaction_id : null;
        $Invoice_List=[];

        if($payment_transaction_id==null){

            $Invoice_List =  $this->accountUtility->getUnpaidDebtorInvoice($business_id,$request->vendor_id);
        }else{

            $Invoice_List =  $this->accountUtility->getUnpaidDebtorInvoiceWithTransactionData($business_id,$request->vendor_id, $payment_transaction_id);            
        }
        


       // return $Invoice_List;

        $html='';

        foreach($Invoice_List as $row){

            $due_amount = ($row->total_amount - (isset($row->total_paid) ? $row->total_paid : '0.00'));
            
            $ref_no = isset($row->transactionDetails[0]->ref_no) ? $row->transactionDetails[0]->ref_no : null;

                         
            $payment_status ='';
            
            if($row->payment_status=='due'){
                $payment_status ='<span class="label bg-yellow">'.strtoupper($row->payment_status).'</span>';
            }else{
                $payment_status ='<span class="label bg-light-blue">'.strtoupper($row->payment_status).'</span>';
            }

            $html.='<tr class="text-center '.(isset($row->sub_amount) ? ($row->trans_id == $payment_transaction_id) ? 'bg-gray' : '' : '' ).'">                    
                    <td>'.$row->transaction_date.'</td>
                    <td>'.$row->document_no.' ('.$ref_no.')</td>
                    <td class="text-center">'.(isset($row->location->name) ? $row->location->name : '').'</td>
                    <td class="text-center">'.$payment_status.'</td>
                    <td class="text-right">'.(number_format($row->total_amount,2,'.',',')).'</td>
                    <td class="text-right">'.(isset($row->total_paid) ? number_format($row->total_paid,2,'.',',') : '0.00').'</td>
                    <td class="text-right">'.(number_format($due_amount,2,'.',',')).'</td>
                    <td><input type="checkbox" class="transaction_id" '.(isset($row->sub_amount) ? (($row->trans_id == $payment_transaction_id) ? 'checked' : '') : '').' value="'.$row->id.'" ></td>
                    <td class="text-center">
                    <input type="hidden" class="form-control text-right ref_no" value="'.$ref_no.'">
                    <input type="hidden" class="form-control text-right balance_amount" value="'.(number_format($due_amount,2,'.','')).'">
                    <input type="number" class="form-control text-right adjusted" '.(isset($row->sub_amount) ? (($row->trans_id == $payment_transaction_id) ? '' : 'disabled') : 'disabled').'  value="'.(isset($row->sub_amount) ? (($row->trans_id == $payment_transaction_id) ? $row->sub_amount : '0.00') : '0.00').'">
                    </td>
                    </tr>';
        }

        return $html;

    }
       
    public function getCreditorDueInvoice(Request $request)
    {   
        $business_id = $request->session()->get('user.business_id');
        $payment_transaction_id = isset($request->transaction_id) ? $request->transaction_id : null;

        $Invoice_List=[];

        if($payment_transaction_id==null){
            $Invoice_List =  $this->accountUtility->getUnpaidCreditorInvoice($business_id, $request->vendor_id);
        }else{
            $Invoice_List =  $this->accountUtility->getUnpaidCreditorInvoiceWithTransactionData($business_id, $request->vendor_id,$payment_transaction_id);
        }

       

        //return $Invoice_List;

        $html='';

        foreach($Invoice_List as $row){

            $due_amount = ($row->total_amount - (isset($row->total_paid) ? $row->total_paid : '0.00'));
            
            $ref_no = isset($row->transactionDetails[0]->ref_no) ? $row->transactionDetails[0]->ref_no : '';
            $desc = isset($row->transactionDetails[0]->desc) ? $row->transactionDetails[0]->desc : '';
                         
            $payment_status ='';
            
            if($row->payment_status=='due'){
                $payment_status ='<span class="label bg-yellow">'.strtoupper($row->payment_status).'</span>';
            }else{
                $payment_status ='<span class="label bg-light-blue">'.strtoupper($row->payment_status).'</span>';
            }

            $html.='<tr class="text-center '.(isset($row->sub_amount) ? ($row->trans_id == $payment_transaction_id) ? 'bg-gray' : '' : '' ).'">                    
                    <td>'.$row->transaction_date.'</td>
                    <td>'.$row->document_no.' ('.$ref_no.')</td>
                    <td class="text-center">'.(isset($row->location->name) ? $row->location->name : '').'</td>
                    <td class="text-center">'.$payment_status.'</td>
                    <td class="text-right">'.(number_format($row->total_amount,2,'.',',')).'</td>
                    <td class="text-right">'.(isset($row->total_paid) ? number_format($row->total_paid,2,'.',',') : '0.00').'</td>
                    <td class="text-right">'.(number_format($due_amount,2,'.',',')).'</td>
                    <td><input type="checkbox" class="transaction_id" '.(isset($row->sub_amount) ? (($row->trans_id == $payment_transaction_id) ? 'checked' : '') : '').' value="'.$row->id.'" ></td>
                    <td class="text-center">
                    <input type="hidden" class="form-control text-right ref_no" value="'.$ref_no.'">
                    <input type="hidden" class="form-control text-right desc" value="'.$desc.'">

                    <input type="hidden" class="form-control text-right balance_amount" value="'.(number_format($due_amount,2,'.','')).'">
                    <input type="number" class="form-control text-right adjusted" '.(isset($row->sub_amount) ? (($row->trans_id == $payment_transaction_id) ? '' : 'disabled') : 'disabled').'  value="'.(isset($row->sub_amount) ? (($row->trans_id == $payment_transaction_id) ? $row->sub_amount : '0.00') : '0.00').'">
                    </td>
                    </tr>';
        } 

        $payees = $this->utility->CreditorPayeeDropdown($request->vendor_id);
        $payee_htlm='';
        if(isset($payees)){
            $payee_htlm='<option value="">None</option>';
            foreach($payees as $payee){
                $payee_htlm.='<option value="'.$payee.'">'.$payee.'</option>';
            }
        }

        return ['inv'=>$html, 'payee'=>$payee_htlm];

         

    }
    
    public function storeDebtorPayment(Request $request)
    {
      

       // return $request->all();
       try{
        DB::beginTransaction();
                $business_id = $request->session()->get('user.business_id');
                $reference_lines= [];
                $details_list =  $request->details_list;
                if(isset($details_list)>0){
                    foreach($details_list as $item){
                        
                        $reference_lines[]= [
                                        'perent_transaction_id'=>$item['perent_transaction_id'],
                                        'ref_no'=>$item['ref_no'],
                                        'sub_amount'=>$item['sub_amount'],
                                        'desc'=>$item['desc']
                                    ];
                    }
                } 

                $payment = [
                    'transaction_id'=> isset($request->transaction_id) ? $request->transaction_id : null,
                    'location_id' =>isset($request->location_id) ? $request->location_id : null,
                    'contact_id' => $request->vendor_id,
                    'transaction_date' => date("Y-m-d H:i:s",strtotime($request->transaction_date)),
                    'payment_method' => strtolower($request->payment_method),
                    'payment_note' => $request->payment_note,
                    'cheque_date' => isset($request->cheque_date) ? date("Y-m-d", strtotime($request->cheque_date)) : null,
                    'cheque_no' => $request->cheque_no,
                    'total_amount' => $request->amount,
                    'debit_account_id' =>$request->account_id,
                ];
            // return $payment;
            
            $this->accountTransactionutility->createReceipt($business_id, $payment['location_id'], $request->vendor_id, $payment, $reference_lines);
            DB::commit();
            return 'done';
        }catch(Exception $ex){
            \Log::error($ex);
            DB::rollback();
            return 'error';
        }

    }

    public function storeCreditorPayment(Request $request)
    {   
       // return $request->all();

       try{
        DB::beginTransaction();
                    $business_id = $request->session()->get('user.business_id');       
                    $reference_lines= [];
                    $details_list =  $request->details_list;
                    if(isset($details_list)>0){
                        foreach($details_list as $item){
                            
                            $reference_lines[]= [
                                            'perent_transaction_id'=>$item['perent_transaction_id'],
                                            'ref_no'=>$item['ref_no'],
                                            'sub_amount'=>$item['sub_amount'],
                                            'desc'=>$item['desc']
                                        ];
                        }
                    }
                    
                    $payment = [
                        'transaction_id'=> isset($request->transaction_id) ? $request->transaction_id : null,
                        'location_id' =>isset($request->location_id) ? $request->location_id : null,
                        'contact_id' => $request->vendor_id,
                        'transaction_date' => date("Y-m-d H:i:s",strtotime($request->transaction_date)),
                        'payment_method' => strtolower($request->payment_method),
                        'payment_note' => $request->payment_note,
                        'cheque_date' => isset($request->cheque_date) ? date("Y-m-d", strtotime($request->cheque_date)) : null,
                        'cheque_no' => $request->cheque_no,
                        'total_amount' => $request->amount,
                        'credit_account_id' =>$request->account_id,
                        'debit_account_id' => $request->debit_account_id,
                        'document_type' => $request->document_type,
                        'payee' => ($request->payment_method=='CHEQUE') ? (isset($request->payee) ? $request->payee : null) : null,
                    ];

                
                    $this->accountTransactionutility->createCreditorVoucher($business_id,$payment['location_id'], $request->vendor_id, $payment,$reference_lines);

                DB::commit();
                return 'done';

        }catch(Exception $ex){
            \Log::error($ex);
            DB::rollback();
            return 'error';
        }
    }
    
    


}
