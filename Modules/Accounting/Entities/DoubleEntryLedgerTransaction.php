<?php

namespace Modules\Accounting\Entities;


use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryTransaction;
use Modules\Accounting\Entities\DoubleEntryTransactionDetail;

class DoubleEntryLedgerTransaction extends Model
{
    protected $fillable = ['transaction_id','account_id','entry_type','amount','is_reconcile','reconcile_at'];
    protected $table='acc_ledger_transactions';

    public function account()
    {
        return $this->belongsTo(DoubleEntryAccount::class, 'account_id');
    }

    public function transaction()
    {
        return $this->belongsTo(DoubleEntryTransaction::class, 'transaction_id');
    }

    public function transactionDetails()
    {
        return $this->hasMany(DoubleEntryTransactionDetail::class, 'transaction_id','transaction_id');
    }

    public function transactionPaymentDetails()
    {
        return $this->hasMany(DoubleEntryTransactionDetail::class, 'perent_transaction_id','transaction_id');
    }


    


    



}
