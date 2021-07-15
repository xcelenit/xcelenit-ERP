<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;

class DoubleEntryTransactionSchemes extends Model
{
    protected $fillable = ['business_id', 'name', 'desc', 'prefix', 'start_number', 'count', 'digit'];
    protected $table='acc_transaction_schemes';

}
