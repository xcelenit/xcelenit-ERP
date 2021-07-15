<?php

namespace Modules\Accounting\Entities;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryAccount;

class DoubleEntryAccountType extends Model
{
    protected $fillable = ['type_code','type','trs_type'];
    protected $table='acc_account_types';

    public function accounts()
    {
        return $this->hasMany(DoubleEntryAccount::class, 'account_type_id');
    }

    public function category()
    {
        return $this->hasMany(DoubleEntryAccountCategory::class, 'account_type_id');
    }

    public static function getDropDown()
    {
        return DoubleEntryAccountType::pluck('type', 'id');
    }

    public function getBalanceDateRange($form_date, $to_date, $location_id=null)
    {   
        $trans=null;

            if($this->trs_type =='CREDIT'){
                $trans_data =  $this->accounts()
                            ->join('acc_ledger_transactions','acc_ledger_transactions.account_id','=','acc_accounts.id')
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
                $trans_data =  $this->accounts()
                                ->join('acc_ledger_transactions','acc_ledger_transactions.account_id','=','acc_accounts.id')
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


}
