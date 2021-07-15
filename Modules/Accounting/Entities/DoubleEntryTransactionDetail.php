<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryTransaction;

class DoubleEntryTransactionDetail extends Model
{
    protected $fillable = ['transaction_id','perent_transaction_id','ref_no','sub_amount','desc'];
    protected $table='acc_transaction_details';

    public function transaction()
    {
        return $this->belongsTo(DoubleEntryTransaction::class, 'transaction_id');
    }
}
