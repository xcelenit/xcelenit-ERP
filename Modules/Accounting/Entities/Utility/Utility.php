<?php

namespace Modules\Accounting\Entities\Utility;

use App\User;
use App\Contact;
use App\Utils\Util;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryAccount;

class Utility extends Util
{

    
    public static function DebitorDropdown($business_id, $prepend_none = true, $append_id = true)
    {
        $all_contacts = Contact::where('business_id', $business_id)
                        ->whereIn('type', ['customer', 'both']);
                        //->active();

        if ($append_id) {
            $all_contacts->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT(name, ' (', contact_id, ')')) AS customer"),
                'id'
                );
        } else {
            $all_contacts->select('id', DB::raw("name as customer"));
        }

        if (!auth()->user()->can('customer.view') && auth()->user()->can('customer.view_own')) {
            $all_contacts->where('contacts.created_by', auth()->user()->id);
        }

        $customers = $all_contacts->pluck('customer', 'id');

        //Prepend none
        if ($prepend_none) {
            $customers = $customers->prepend(__('lang_v1.none'), '');
        }

        return $customers;
    }

    public static function CreditorDropdown($business_id, $prepend_none = true, $append_id = true)
    {
        $all_contacts = Contact::where('business_id', $business_id)
                        ->whereIn('type', ['supplier', 'both']);
                        //->active();

        if ($append_id) {
            $all_contacts->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT(name, ' (', contact_id, ')')) AS customer"),
                'id'
                );
        } else {
            $all_contacts->select('id', DB::raw("name as customer"));
        }

        if (!auth()->user()->can('customer.view') && auth()->user()->can('customer.view_own')) {
            $all_contacts->where('contacts.created_by', auth()->user()->id);
        }

        $customers = $all_contacts->pluck('customer', 'id');

        //Prepend none
      //  if ($prepend_none) {
            $customers = $customers->prepend(__('lang_v1.none'), '');
       // }

        return $customers;
    }

    public static function vendorDropdown($business_id, $prepend_none = true, $append_id = true)
    {
        $all_contacts = Contact::where('business_id', $business_id)
                        ->whereIn('type', ['supplier','customer', 'both']);
                        //->active();

        if ($append_id) {
            $all_contacts->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT(name, ' (', contact_id, ')')) AS customer"),
                'id'
                );
        } else {
            $all_contacts->select('id', DB::raw("name as customer"));
        }

        // if (!auth()->user()->can('customer.view') && auth()->user()->can('customer.view_own')) {
        //     $all_contacts->where('contacts.created_by', auth()->user()->id);
        // }

        $customers = $all_contacts->pluck('customer', 'id');

        //Prepend none
        if ($prepend_none) {
            $customers = $customers->prepend('All', '');
        }

        return $customers;
    }
 
    public static function CreditorPayeeDropdown($vendor_id)
    { 
        
        $contact = Contact::where('id', $vendor_id)
                        ->whereIn('type', ['supplier', 'both'])
                        ->select('id', 'name','supplier_business_name','custom_field1','custom_field2','custom_field3')->first();
        
        $payee_list =[];

        if(isset($contact )){
            if($contact->supplier_business_name!=null){
                $payee_list[] = $contact->supplier_business_name;
            }
    
            if($contact->name!=null){
                $payee_list[] = $contact->name;
            }
            if($contact->custom_field1!=null){
                $payee_list[] = $contact->custom_field1;
            }
            if($contact->custom_field2!=null){
                $payee_list[] = $contact->custom_field2;
            }
    
            if($contact->custom_field3!=null){
                $payee_list[] = $contact->custom_field3;
            }

            $payee_list[] = 'CASH';
        }
       
                
       
        
        

        return $payee_list;
    }

    public function getDocumentTypeDropDown()
    {
       $document_type_array = collect(config('accounting.document_type'));         
       $list = $document_type_array->pluck('type','code');

       $list = $list->prepend('All', '');
       
       return $list;
    }

    public function getUserDropDown($business_id)
    {
       $users_data = User::where('business_id',$business_id)
       ->where('allow_login',1);
       
       if(Auth::user()->id != 1){
           $users_data->whereNotIn('id',[1]);
       }                        
       
       $users =  $users_data->where('status','active')
               ->where('user_type','user')->pluck('username','id'); 

       $users = $users->prepend('All', '');
       return $users;
       

    }

    public function getBankAccountsWithBalance($business_id)
    {
        $bank_accounts = DoubleEntryAccount::where('business_id',$business_id)
                                            ->where('is_bank_ac',1)
                                            ->where('is_active',1)
                                            ->select('account_name','account_no','balance')
                                            ->where('account_type_id',1)->orderBy('account_code','ASC')->get();
        return $bank_accounts;
    }

    public function getInHandAccountsWithBalance($business_id)
    {
        $bank_accounts = DoubleEntryAccount::where('business_id',$business_id)
                                            ->whereIn('id',config('accounting.default_in_hand_account_ids'))
                                            ->where('is_active',1)                                             
                                            ->select('account_name','account_no','balance')
                                            ->where('account_type_id',1)->orderBy('account_code','ASC')->get();
        return $bank_accounts;
    }
    
    public function getDashboardDataByDate($business_id, $start_date, $end_date)
    {
         $total_income = DoubleEntryAccount::where('business_id',$business_id)
                                            ->where('account_type_id',config('accounting.default_account_type.income'))->sum('balance');

        $total_expense =  $total_income = DoubleEntryAccount::where('business_id',$business_id)
                                            ->where('account_type_id',config('accounting.default_account_type.expenses'))->sum('balance');
        
    }

    public static function getSalesInvoiceWithDetails($transaction_id,$type)
    {
        
                                    //whereBetween(DB::raw('date(transactions.transaction_date)'),['2021-01-10','2021-01-11'])
        $Transactions = Transaction::leftJoin('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')
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
                                    ->whereIn('transactions.type',[$type])
                                    ->where('transactions.id',$transaction_id)
                                    // ->whereIn('transactions.id',[318, 507,509,510])
                                    ->where('transactions.is_quotation',0)
                                    ->where('transactions.status','final')
                                    // ->where('transactions.business_id')
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
                                    ->first();
        return $Transactions;
    }

    
}
