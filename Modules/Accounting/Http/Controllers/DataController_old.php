<?php

namespace Modules\Accounting\Http\Controllers;

use DB;
use Menu;
use Exception;
use App\Transaction;
use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Modules\Accounting\Entities\Utility\Utility;
use Modules\Accounting\Entities\DoubleEntryTransaction;
use Modules\Accounting\Entities\Utility\AccountTransactionUtility;

class DataController extends Controller
{
    /**
     * Defines user permissions for the module. 
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'accounting.access_double_entry_accounting',
                'label' =>  'Access Doble Entry Accounting',
                'default' => false
            ],            
        ];
    }
    

    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        
       

        if ((auth()->user()->can('accounting.access_double_entry_accounting'))) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                        action('\Modules\Accounting\Http\Controllers\AccountingController@index'),
                        'Accounting',
                        ['icon' => 'fa fas fa-balance-scale', 'style' => config('app.env') == 'demo' ? 'background-color: #ff851b;' : '', 'active' => request()->segment(1) == 'accounting']
                    )
                ->order(51);
            });
        }
    }
    
    //[2021-01-24 17:11:05] local.EMERGENCY: {"business_id":1,"location_id":"2","type":"sell","status":"final","contact_id":"1","customer_group_id":null,"invoice_no":"10885","ref_no":"","total_before_tax":2560,"transaction_date":"2021-01-24T11:41:05.360736Z","tax_id":null,"discount_type":"percentage","discount_amount":0,"tax_amount":0,"final_total":2560,"additional_notes":null,"staff_note":null,"created_by":1,"is_direct_sale":0,"commission_agent":null,"is_quotation":0,"shipping_details":null,"shipping_address":null,"shipping_status":null,"delivered_to":null,"shipping_charges":0,"exchange_rate":1,"selling_price_group_id":"0","pay_term_number":null,"pay_term_type":null,"is_suspend":0,"is_recurring":0,"recur_interval":1,"recur_interval_type":"days","subscription_repeat_on":null,"subscription_no":null,"recur_repetitions":0,"order_addresses":null,"sub_type":null,"rp_earned":0,"rp_redeemed":0,"rp_redeemed_amount":0,"is_created_from_api":0,"types_of_service_id":null,"packing_charge":0,"packing_charge_type":null,"service_custom_field_1":null,"service_custom_field_2":null,"service_custom_field_3":null,"service_custom_field_4":null,"round_off_amount":0,"import_batch":null,"import_time":null,"res_table_id":null,"res_waiter_id":null,"updated_at":"2021-01-24 17:11:05","created_at":"2021-01-24 17:11:05","id":12615,"payment_lines":[{"id":11004,"transaction_id":12615,"business_id":1,"is_return":0,"amount":"2560.0000","method":"cash","transaction_no":null,"card_transaction_number":null,"card_number":null,"card_type":"credit","card_holder_name":null,"card_month":null,"card_year":null,"card_security":null,"cheque_number":null,"bank_account_number":null,"paid_on":"2021-01-24 17:11:05","created_by":1,"is_advance":0,"payment_for":1,"parent_id":null,"note":"Test PMT Note","document":null,"payment_ref_no":"SP2021\/10909","account_id":null,"created_at":"2021-01-24 17:11:05","updated_at":"2021-01-24 17:11:05"}],"sell_lines":[{"id":28429,"transaction_id":12615,"product_id":19,"variation_id":19,"quantity":1,"quantity_returned":"0.0000","unit_price_before_discount":"270.0000","unit_price":"270.0000","line_discount_type":"fixed","line_discount_amount":"0.0000","unit_price_inc_tax":"270.0000","item_tax":"0.0000","tax_id":null,"discount_id":null,"lot_no_line_id":null,"sell_line_note":"","res_service_staff_id":null,"res_line_order_status":null,"parent_sell_line_id":null,"children_type":"","sub_unit_id":null,"created_at":"2021-01-24 17:11:05","updated_at":"2021-01-24 17:11:05"},{"id":28430,"transaction_id":12615,"product_id":92,"variation_id":92,"quantity":1,"quantity_returned":"0.0000","unit_price_before_discount":"390.0000","unit_price":"390.0000","line_discount_type":"fixed","line_discount_amount":"0.0000","unit_price_inc_tax":"390.0000","item_tax":"0.0000","tax_id":null,"discount_id":null,"lot_no_line_id":null,"sell_line_note":"","res_service_staff_id":null,"res_line_order_status":null,"parent_sell_line_id":null,"children_type":"","sub_unit_id":null,"created_at":"2021-01-24 17:11:05","updated_at":"2021-01-24 17:11:05"},{"id":28431,"transaction_id":12615,"product_id":46,"variation_id":46,"quantity":1,"quantity_returned":"0.0000","unit_price_before_discount":"1900.0000","unit_price":"1900.0000","line_discount_type":"fixed","line_discount_amount":"0.0000","unit_price_inc_tax":"1900.0000","item_tax":"0.0000","tax_id":null,"discount_id":null,"lot_no_line_id":null,"sell_line_note":"","res_service_staff_id":null,"res_line_order_status":null,"parent_sell_line_id":null,"children_type":"","sub_unit_id":null,"created_at":"2021-01-24 17:11:05","updated_at":"2021-01-24 17:11:05"}],"contact":{"id":1,"business_id":1,"type":"customer","supplier_business_name":null,"name":"Walk-In Customer","prefix":null,"first_name":null,"middle_name":null,"last_name":null,"email":null,"contact_id":"CO0001","contact_status":"active","tax_number":null,"city":null,"state":null,"country":null,"address_line_1":null,"address_line_2":null,"zip_code":null,"dob":null,"mobile":"","landline":null,"alternate_number":null,"pay_term_number":null,"pay_term_type":null,"credit_limit":"0.0000","created_by":1,"balance":"0.0000","total_rp":0,"total_rp_used":0,"total_rp_expired":0,"is_default":1,"shipping_address":null,"position":null,"customer_group_id":null,"custom_field1":null,"custom_field2":null,"custom_field3":null,"custom_field4":null,"custom_field5":null,"custom_field6":null,"custom_field7":null,"custom_field8":null,"custom_field9":null,"custom_field10":null,"deleted_at":null,"created_at":"2020-11-11 23:49:42","updated_at":"2020-11-11 23:49:42"}}  
    public function after_sale_saved_acc($data)
    {   
        if(!(config('accounting.link_transaction.sales'))){
            return null;
        }

        $transaction_data = $data['transaction'];
       // \Log::emergency($data['transaction']);
        //$input = $data['input'];

        $transaction_details = Utility::getSalesInvoiceWithDetails($transaction_data['id'],'sell');      

        if(!isset($transaction_details)){
            return null;
        }

        $has_transaction = DoubleEntryTransaction::where('sys_transaction_id',$transaction_data['id'])
                                ->where('document_type','SAL')
                                ->where('is_canceled',0)->first();
        $transaction_id=null;
        if(isset($has_transaction)){
            if($has_transaction->status>1){
                return null;
            }else{
                $transaction_id=$has_transaction->id;
            }
        }

        $transaction=[
            'location_id'=>$transaction_details->location_id,
            'contact_id'=>$transaction_details->contact_id,
            'transaction_date'=>$transaction_details->transaction_date,
            'total_amount'=>$transaction_details->final_total,
            'invoice_no'=>$transaction_details->invoice_no,
            'total_cost'=> isset($transaction_details->sale_cost) ? ($transaction_details->sale_cost) : 0,
            'sys_transaction_id'=>$transaction_details->id,
            'transaction_id'=>$transaction_id,
        ];


        $payments=[];

        foreach($transaction_details->payment_lines as $payment_line){
            //return  $payment_line;

            $payment['location_id'] =$transaction_details->location_id;
            $payment['contact_id'] = $transaction_details->contact_id;
            $payment['transaction_date'] =$payment_line->paid_on;
            $payment['total_amount'] =$payment_line->amount;
            $payment['payment_method'] =$payment_line->method;
            $payment['sys_transaction_id'] = $payment_line->id;
            
            $transaction_id_rc=null;
            if(isset($has_transaction)){
                $has_transaction_rc = DoubleEntryTransaction::where('sys_transaction_id',$payment_line->id)
                         ->where('document_type','RCP')
                         ->where('is_canceled',0)->first();

                if(isset($has_transaction_rc)){
                    if($has_transaction_rc->status>1){
                        
                    }else{
                        $transaction_id_rc=$has_transaction_rc->id;
                    }
                }
            }
            

            $payment['transaction_id']=$transaction_id_rc;
            
            $payments[]= $payment;

        }

       

        try{
            DB::beginTransaction();
            $accountTras = new AccountTransactionUtility();        
            return $accountTras->createSalesEntry($transaction_data['business_id'],$transaction, $payments);
            DB::commit();
            return 'done';
        }catch(Exception $ex){
            DB::rollback();
            \Log::error($ex);
            return 'error';
        }
       

       
    }

    //[2021-01-24 16:39:05] local.EMERGENCY: {"ref_no":"EP2021\/0028","transaction_date":"2021-01-24 16:38:00","location_id":"1","final_total":11120,"expense_for":null,"additional_notes":"wb test","expense_category_id":"3","tax_id":null,"contact_id":"69","business_id":1,"created_by":1,"type":"expense","status":"final","payment_status":"due","total_before_tax":11120,"updated_at":"2021-01-24 16:39:05","created_at":"2021-01-24 16:39:05","id":12612}  

    public function after_expense_saved_acc($data)
    {   

        if(!(config('accounting.link_transaction.expenses'))){
            return null;
        }

        //\Log::emergency($data['transaction']);
        //$this->moduleUtil->getModuleData('after_expense_saved_acc', ['transaction' => $transaction, 'input' => null]);

        $transaction_data = $data['transaction'];     
        
        $has_transaction = DoubleEntryTransaction::where('sys_transaction_id',$transaction_data['id'])
                                                  ->where('document_type','EXP')
                                                  ->where('is_canceled',0)->first();
                                                  
        $transaction_id=null;
        if(isset($has_transaction)){
            if($has_transaction->status>1){
                return null;
            }else{
                $transaction_id=$has_transaction->id;
            }
        }

        $transaction=[
            'location_id'=>$transaction_data['location_id'],
            'contact_id'=>$transaction_data['contact_id'],
            'category_id'=>$transaction_data['expense_category_id'],
            'transaction_date'=>$transaction_data['transaction_date'],
            'total_amount'=>$transaction_data['final_total'],
            'expenses_ref'=>$transaction_data['ref_no'],
            'supplier_invoice_no'=>$transaction_data['expenses_ref'], 
            'sys_transaction_id'=>$transaction_data['id'],
            'expense_note'=>$transaction_data['additional_notes'],
            'transaction_id' =>$transaction_id,
        ];

        $accountTras = new AccountTransactionUtility();
        
        $accountTras->createExpenseEntry($transaction_data['business_id'], $transaction);



    }

    //[2021-01-24 19:06:00] local.EMERGENCY: {"ref_no":"PO2021\/0239","status":"received","contact_id":"2","transaction_date":"2021-01-24 19:04:00","total_before_tax":2675,"location_id":"1","discount_type":null,"discount_amount":0,"tax_id":null,"tax_amount":0,"shipping_details":null,"shipping_charges":0,"final_total":2675,"additional_notes":"Test GRN ADN","exchange_rate":"1","pay_term_number":"0","pay_term_type":null,"business_id":1,"created_by":1,"type":"purchase","payment_status":"due","document":null,"updated_at":"2021-01-24 19:06:00","created_at":"2021-01-24 19:06:00","id":12628,"purchase_lines":[{"id":2678,"transaction_id":12628,"product_id":38,"variation_id":38,"quantity":100,"pp_without_discount":"26.7500","discount_percent":"0.00","purchase_price":"26.7500","purchase_price_inc_tax":"26.7500","item_tax":"0.0000","tax_id":null,"quantity_sold":"0.0000","quantity_adjusted":"0.0000","quantity_returned":"0.0000","mfg_quantity_used":"0.0000","mfg_date":null,"exp_date":null,"lot_number":null,"sub_unit_id":null,"foc":"0.0000","created_at":"2021-01-24 19:06:00","updated_at":"2021-01-24 19:06:00","product":{"id":38,"name":"Vanilla Flavoured Yoghurt 90g","business_id":1,"type":"single","unit_id":7,"sub_unit_ids":null,"brand_id":1,"category_id":7,"sub_category_id":null,"tax":null,"tax_type":"exclusive","enable_stock":1,"alert_quantity":"0.0000","sku":"038","barcode_type":"C128","expiry_period":null,"expiry_period_type":null,"enable_sr_no":0,"weight":null,"product_custom_field1":"Vanilla Yoghurt 90g","product_custom_field2":"32","product_custom_field3":"2.5","product_custom_field4":null,"image":"1605181059_20.jpg","product_description":null,"created_by":2,"warranty_id":null,"is_inactive":0,"repair_model_id":null,"not_for_selling":0,"created_at":"2020-11-12 16:35:14","updated_at":"2020-11-23 12:02:39","image_url":"http:\/\/localhost:1313\/uploads\/img\/1605181059_20.jpg"}}]}  

    public function after_purchases_saved_acc($data)
    {
        
        //\Log::emergency($data['transaction']);

        if(!(config('accounting.link_transaction.purchases'))){
            return null;
        }
         
        $transaction_data = $data['transaction'];  

        if($transaction_data['status']!='received')
        {            
           return null;
        }

        $has_transaction = DoubleEntryTransaction::where('sys_transaction_id', $transaction_data['id'])
                                                  ->where('document_type','GRN')
                                                  ->where('is_canceled',0)->first();                                                  
        $transaction_id=null;
        
        if(isset($has_transaction)){
            if($has_transaction->status>1){
                return null;
            }else{
                $transaction_id=$has_transaction->id;
            }
        }
         
        $transaction=[
            'location_id'=>$transaction_data['location_id'],
            'contact_id'=>$transaction_data['contact_id'],
            'transaction_date'=>$transaction_data['transaction_date'],
            'total_amount'=>$transaction_data['final_total'],
            'purchase_ref'=>$transaction_data['invoice_no'],
            'supplier_invoice_no'=>'', 
            'grn_note'=>$transaction_data['additional_notes'],
            'sys_transaction_id'=>$transaction_data['id'],
            'transaction_id'=>$transaction_id,
        ];

         $accountTras = new AccountTransactionUtility();
         $accountTras->createPurchaseEntry($transaction_data['business_id'],$transaction);

    }

    public function after_credit_note_saved_acc($data)
    {
        if(!(config('accounting.link_transaction.credit_note'))){
            return null;
        }

        $transaction_data = $data['transaction'];
       //\Log::emergency($data['transaction']);
        //$input = $data['input'];

        $transaction_details = Utility::getSalesInvoiceWithDetails($transaction_data['id'],'sell_return');      

        if(!isset($transaction_details)){
            return null;
        }

        $has_transaction = DoubleEntryTransaction::where('sys_transaction_id',$transaction_data['id'])
                                                 ->where('document_type','CDN')
                                                 ->where('is_canceled',0)
                                                 ->first();

        $transaction_id=null;
        if(isset($has_transaction)){
            if($has_transaction->status>1){
                return null;
            }else{
                $transaction_id=$has_transaction->id;
            }
        }

        $transaction=[
            'location_id'=>$transaction_details->location_id,
            'contact_id'=>$transaction_details->contact_id,
            'transaction_date'=>$transaction_details->transaction_date,
            'total_amount'=>$transaction_details->final_total,
            'invoice_no'=>$transaction_details->invoice_no,
            'total_cost'=> isset($transaction_details->return_cost) ? ($transaction_details->return_cost) : 0,
            'sys_transaction_id'=>$transaction_details->id,
            'transaction_id'=>$transaction_id,
        ];


        $payments=[];

        $accountTras = new AccountTransactionUtility();        
        return $accountTras->createCreditNote($transaction_data['business_id'],$transaction, $payments);

    }

}
