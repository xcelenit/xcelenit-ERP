<?php

namespace App\Custom;

use App\Transaction;
use Illuminate\Database\Eloquent\Model;

class CustomeTransactionUtil extends Model
{
    //

    public function getPurchaseSupplier($business_id, $transaction_id)
    {
        $supplier = Transaction::join('contacts as ct','transactions.contact_id','=','ct.id')                            
                                ->where('transactions.business_id', $business_id)
                                ->where('transactions.id', $transaction_id)
                                ->where('transactions.type', 'purchase')
                                ->select('ct.supplier_business_name','ct.name','ct.contact_id','transactions.ref_no','transactions.transaction_date')
                                ->first();
        return $supplier;
    }

    public function getCreditNoteValue($customer_id, $cn_ref_no)
    {
        $cn_data = Transaction::where('transactions.contact_id', $customer_id)
                                //->where('transactions.business_id', $business_id)
                                ->where('transactions.invoice_no', $cn_ref_no)
                                ->where('transactions.payment_status', 'due')
                                ->where('transactions.type', 'sell_return')->first();

            return $cn_data;
    }
}
