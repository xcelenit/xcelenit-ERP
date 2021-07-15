<?php

namespace Modules\Accounting\Entities;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryAccountType;
use Modules\Accounting\Entities\DoubleEntryAccountCategory;
use Modules\Accounting\Entities\DoubleEntryLedgerTransaction;

class DoubleEntryAccount extends Model
{
    protected $fillable = ['business_id','account_type_id','category_id','created_by','account_code','account_name','account_no','balance','is_active','is_default'];
    protected $table='acc_accounts'; 

    public function account_type()
    {
        return $this->belongsTo(DoubleEntryAccountType::class, 'account_type_id');
    }

    public function category()
    {
        return $this->belongsTo(DoubleEntryAccountCategory::class, 'category_id');
    }

    public function created_user()
    { 
        return $this->belongsTo(User::class, 'created_by');
    }

    
    public function ledgerTransactions()
    {
        return $this->hasMany(DoubleEntryLedgerTransaction::class, 'account_id');
    }


    public static function getNewAccountCode($category_id)
    {
        //100-01-0001 
        //100-01-0002
        $account_code ='';

        $category = DoubleEntryAccountCategory::find($category_id);
        $type = DoubleEntryAccountType::find($category->account_type_id);
        
        $noOfAccountsUnderCategory = Self::where('category_id',$category_id)->count();

        $acc_no_last_digit = sprintf("%04d", ($noOfAccountsUnderCategory+1));

        $account_code = $type->type_code.'-'.$category->category_code.'-'.$acc_no_last_digit;

        return  $account_code;


    }

    public function getBalance($vendor_id=null, $date =null)
    {   
        $trans=null;

            if($this->account_type->trs_type =='CREDIT'){
                $trans_data =  $this->ledgerTransactions()
                            ->join('acc_transactions','acc_transactions.id','=','acc_ledger_transactions.transaction_id')
                            ->where('acc_transactions.is_canceled',0);

                            if($vendor_id!=null){
                                $trans_data->where('acc_transactions.vendor_id',$vendor_id);
                            }
                            if($date!=null){
                                $trans_data->where(DB::raw('date(acc_transactions.transaction_date)'),'<',$date);
                            }

                            $trans_data->orderBy('acc_transactions.transaction_date', 'ASC')
                                       ->orderBy('acc_ledger_transactions.id', 'ASC');

                            $trans = $trans_data->select(DB::raw("SUM( IF(acc_ledger_transactions.entry_type='CR', amount, -1*amount) ) as account_balance"))->first();
            }else{
                $trans_data =  $this->ledgerTransactions()
                                ->join('acc_transactions','acc_transactions.id','=','acc_ledger_transactions.transaction_id')
                                ->where('acc_transactions.is_canceled',0);

                                if($vendor_id!=null){
                                    $trans_data->where('acc_transactions.vendor_id',$vendor_id);
                                }

                                if($date!=null){
                                    $trans_data->where(DB::raw('date(acc_transactions.transaction_date)'),'<',$date);
                                   // $trans_data->where('acc_transactions.transaction_date','<',$date);
                                }

                                $trans_data  //->groupBy('acc_ledger_transactions.id')
                                           ->orderBy('acc_transactions.transaction_date', 'ASC');
                                           //->orderBy('acc_ledger_transactions.id', 'ASC');

                            $trans = $trans_data->select(DB::raw("SUM( IF(acc_ledger_transactions.entry_type='DR', amount, -1*amount) ) as account_balance"))->first();
            }
 
         return ($trans->account_balance!=null) ? $trans->account_balance : 0.00;
    }

    public function getBalanceAsAtDate($vendor_id=null, $date =null)
    {   
        $trans=null;

            if($this->account_type->trs_type =='CREDIT'){
                $trans_data =  $this->ledgerTransactions()
                            ->join('acc_transactions','acc_transactions.id','=','acc_ledger_transactions.transaction_id')
                            ->where('acc_transactions.is_canceled',0);

                            if($vendor_id!=null){
                                $trans_data->where('acc_transactions.vendor_id',$vendor_id);
                            }
                            if($date!=null){
                                $trans_data->where(DB::raw('date(acc_transactions.transaction_date)'),'<=',$date);
                            }

                            $trans_data->orderBy('acc_transactions.transaction_date', 'ASC')
                                       ->orderBy('acc_ledger_transactions.id', 'ASC');

                            $trans = $trans_data->select(DB::raw("SUM( IF(acc_ledger_transactions.entry_type='CR', amount, -1*amount) ) as account_balance"))->first();
            }else{
                $trans_data =  $this->ledgerTransactions()
                                ->join('acc_transactions','acc_transactions.id','=','acc_ledger_transactions.transaction_id')
                                ->where('acc_transactions.is_canceled',0);

                                if($vendor_id!=null){
                                    $trans_data->where('acc_transactions.vendor_id',$vendor_id);
                                }

                                if($date!=null){
                                    $trans_data->where(DB::raw('date(acc_transactions.transaction_date)'),'<=',$date);
                                   // $trans_data->where('acc_transactions.transaction_date','<',$date);
                                }

                                $trans_data  //->groupBy('acc_ledger_transactions.id')
                                           ->orderBy('acc_transactions.transaction_date', 'ASC');
                                           //->orderBy('acc_ledger_transactions.id', 'ASC');

                            $trans = $trans_data->select(DB::raw("SUM( IF(acc_ledger_transactions.entry_type='DR', amount, -1*amount) ) as account_balance"))->first();
            }
 
         return ($trans->account_balance!=null) ? $trans->account_balance : 0.00;
    }

    public function getBalanceDateRange($form_date, $to_date, $location_id=null)
    {   
        $trans=null;

            if($this->account_type->trs_type =='CREDIT'){
                $trans_data =  $this->ledgerTransactions()
                            ->join('acc_transactions','acc_transactions.id','=','acc_ledger_transactions.transaction_id')
                            ->where('acc_transactions.is_canceled',0);

                             if(isset($location_id)){
                                $trans_data->where('acc_transactions.location_id', $location_id);
                             }
                             
                            $trans_data->where(DB::raw('date(acc_transactions.transaction_date)'),'>=',$form_date)
                                       ->where(DB::raw('date(acc_transactions.transaction_date)'),'<=',$to_date);
                             

                            $trans_data->orderBy('acc_transactions.transaction_date', 'ASC')
                                       ->orderBy('acc_ledger_transactions.id', 'ASC');

                            $trans = $trans_data->select(DB::raw("SUM( IF(acc_ledger_transactions.entry_type='CR', amount, -1*amount) ) as account_balance"))->first();
            }else{
                $trans_data =  $this->ledgerTransactions()
                                ->join('acc_transactions','acc_transactions.id','=','acc_ledger_transactions.transaction_id')
                                ->where('acc_transactions.is_canceled',0);

                               
                                if(isset($location_id)){
                                    $trans_data->where('acc_transactions.location_id', $location_id);
                                 }
                                 
                                $trans_data->where(DB::raw('date(acc_transactions.transaction_date)'),'>=',$form_date)
                                           ->where(DB::raw('date(acc_transactions.transaction_date)'),'<=',$to_date);
                                   // $trans_data->where('acc_transactions.transaction_date','<',$date);
                                

                                $trans_data  //->groupBy('acc_ledger_transactions.id')
                                           ->orderBy('acc_transactions.transaction_date', 'ASC');
                                           //->orderBy('acc_ledger_transactions.id', 'ASC');

                            $trans = $trans_data->select(DB::raw("SUM( IF(acc_ledger_transactions.entry_type='DR', amount, -1*amount) ) as account_balance"))->first();
            }
 
         return ($trans->account_balance!=null) ? $trans->account_balance : 0.00;
    }

    public function getSummary($vendor_id=null)
    {   
        $trans=null;

            if($this->account_type->trs_type =='CREDIT'){
                $trans_data =  $this->ledgerTransactions()
                            ->join('acc_transactions AS AT','AT.id','=','acc_ledger_transactions.transaction_id')
                            ->where('AT.is_canceled',0);

                            if($vendor_id!=null){
                                $trans_data->where('AT.vendor_id',$vendor_id);
                            }

                            $trans = $trans_data->select(
                                DB::raw("SUM( IF(acc_ledger_transactions.entry_type='CR' AND AT.is_opening_bl='1', acc_ledger_transactions.amount, 0) ) as opening_balance"),                                
                                DB::raw("SUM( IF(acc_ledger_transactions.entry_type='CR', acc_ledger_transactions.amount, 0) ) as total_credit"),
                                DB::raw("SUM( IF(acc_ledger_transactions.entry_type='DR', acc_ledger_transactions.amount, 0) ) as total_debit"),
                                DB::raw("SUM( IF(acc_ledger_transactions.entry_type='CR', acc_ledger_transactions.amount, -1 * acc_ledger_transactions.amount) ) as account_balance")
                                )->first();
            }else{
                $trans_data =  $this->ledgerTransactions()
                                ->join('acc_transactions AS AT','AT.id','=','acc_ledger_transactions.transaction_id')
                                ->where('AT.is_canceled',0);

                                if($vendor_id!=null){
                                    $trans_data->where('AT.vendor_id',$vendor_id);
                                }
                        
                            $trans = $trans_data->select(
                                    DB::raw("SUM( IF(acc_ledger_transactions.entry_type='DR' AND AT.is_opening_bl='1', acc_ledger_transactions.amount, 0) ) as opening_balance"),                                
                                    DB::raw("SUM( IF(acc_ledger_transactions.entry_type='CR', acc_ledger_transactions.amount, 0) ) as total_credit"),
                                    DB::raw("SUM( IF(acc_ledger_transactions.entry_type='DR', acc_ledger_transactions.amount, 0) ) as total_debit"),                                
                                    DB::raw("SUM( IF(acc_ledger_transactions.entry_type='DR', acc_ledger_transactions.amount, -1 * acc_ledger_transactions.amount) ) as account_balance")
                                )->first();
            }
            
         return $trans;
    }


    public static function getReceiptDebitAccountList()
    {   

        $business_id = request()->session()->get('user.business_id');
        $accounts = self::where('business_id',$business_id)->where('account_type_id',1)
                    ->where('category_id',2)
                    ->where('is_active',1)
                    ->whereNotIn('id',[
                        config('accounting.default_account_ids.debtor'),
                        config('accounting.default_account_ids.inventory')
                        ]) 
                    ->select(DB::raw("CONCAT(account_name, ' (', account_no, ')') AS account_desc"),'id')                   
                    ->pluck('account_desc','id');
        
        $accounts = $accounts->prepend('None', '');
        return $accounts;
    }

    public static function getPettyCashAccountList()
    {   
        $business_id = request()->session()->get('user.business_id');
        $accounts = self::where('business_id',$business_id)->where('account_type_id',1)
                    ->where('is_active',1)
                    ->where('category_id',2)
                    ->whereIn('id',config('accounting.petty_cash_account_ids'))
                    ->whereNotIn('id',[
                        config('accounting.default_account_ids.debtor'),
                        config('accounting.default_account_ids.inventory')
                        ]) 
                    ->select(DB::raw("CONCAT(account_name, ' (', account_no, ')') AS account_desc"),'id')                   
                    ->pluck('account_desc','id');
        
        $accounts = $accounts->prepend('None', '');
        return $accounts;
    }

    public static function getBankAccountList()
    {   
        $business_id = request()->session()->get('user.business_id');
        $accounts = self::where('business_id',$business_id)->where('account_type_id',1)
                     ->where('is_active',1)                    
                    ->where('category_id',2)
                    ->where('is_bank_ac',1)
                    // ->whereIn('id',config('accounting.petty_cash_account_ids'))
                    ->whereNotIn('id',[
                        config('accounting.default_account_ids.debtor'),
                        config('accounting.default_account_ids.inventory')
                        ]) 
                    ->select(DB::raw("CONCAT(account_name, ' (', account_no, ')') AS account_desc"),'id')                   
                    ->pluck('account_desc','id');
        
        $accounts = $accounts->prepend('None', '');
        return $accounts;
    }

    public static function getLedgerAccountList()
    {   

        $business_id = request()->session()->get('user.business_id');
        $accounts = self::where('business_id',$business_id)
                    ->where('is_active',1)
                    // ->where('account_type_id',1)
                    // ->where('category_id',2)
                    ->whereNotIn('id',[
                        config('accounting.default_account_ids.debtor'),
                        config('accounting.default_account_ids.creditor')
                        ]) 
                    ->select(DB::raw("CONCAT(account_name, ' (', account_no, ')') AS account_desc"),'id')                   
                    ->pluck('account_desc','id');
        
        $accounts = $accounts->prepend('None', '');
        return $accounts;
    }

    public static function getLedgerAccountListAll($not_in=null)
    {   

        $business_id = request()->session()->get('user.business_id');
        $accounts = self::where('business_id',$business_id)
                    ->where('is_active',1);
                    // ->where('category_id',2)
                    if(isset($not_in)){
                        $accounts->whereNotIn('id',$not_in);
                    }
                    

        $accounts =    $accounts ->select(DB::raw("CONCAT(account_name, ' (', account_no, ')') AS account_desc"),'id')                   
                    ->pluck('account_desc','id');
        
        $accounts = $accounts->prepend('None', '');
        return $accounts;
    }

    public static function getVoucherDebitAccountList()
    {   

        $not_in =[
            config('accounting.default_account_ids.debtor'),
            config('accounting.default_account_ids.cost_of_sale'),
            config('accounting.default_account_ids.cash_in_hand'),
            config('accounting.default_account_ids.cheque_in_hand'),
            config('accounting.default_account_ids.card_payment'),
            config('accounting.default_account_ids.inventory'),

        ];

        $business_id = request()->session()->get('user.business_id');
        $accounts = self::where('business_id',$business_id)
                    ->where('is_active',1)
                    ->whereIn('account_type_id',[1,2,3]);
                    // ->where('category_id',2)
                     
                    $accounts->whereNotIn('id',$not_in);

        $accounts =    $accounts ->select(DB::raw("CONCAT(account_name, ' (', account_no, ')') AS account_desc"),'id')                   
                    ->pluck('account_desc','id');
        
        $accounts = $accounts->prepend('None', '');
        return $accounts;
    }



   
}
