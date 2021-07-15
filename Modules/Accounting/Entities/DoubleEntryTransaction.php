<?php

namespace Modules\Accounting\Entities;

use App\User;
use App\Contact;
use App\BusinessLocation;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryTransaction;
use Modules\Accounting\Entities\DoubleEntryLedgerTransaction;
use Modules\Accounting\Entities\DoubleEntryTransactionDetail;

class DoubleEntryTransaction extends Model
{
    protected $fillable = [];
    protected $table='acc_transactions';
    // protected $appends = ['debit_account','credit_account'];

    public function ledgerTransactions()
    {
        return $this->hasMany(DoubleEntryLedgerTransaction::class, 'transaction_id');
    }

    public function transactionDetails()
    {
        return $this->hasMany(DoubleEntryTransactionDetail::class, 'transaction_id');
    }
    
    public function added_user()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }
    
    public function vendor()
    {
        return $this->belongsTo(Contact::class, 'vendor_id');
    }

    public function transactionPaymentDetails()
    {
        return $this->hasMany(self::class, 'perent_transaction_id','id');
    }

    public function getDebitAccountAttribute()
    {
        $name = $this->ledgerTransactions()
                ->join('acc_accounts AS AC','AC.id','=','acc_ledger_transactions.account_id')
                ->where('entry_type','DR')->select('AC.account_name')->get();
        return $name[0]->account_name;
    }
    public function getCreditAccountAttribute()
    {

        $name = $this->ledgerTransactions()
        ->join('acc_accounts AS AC','AC.id','=','acc_ledger_transactions.account_id')
        ->where('entry_type','CR')->select('AC.account_name')->get();
        return $name[0]->account_name;
    }
}
