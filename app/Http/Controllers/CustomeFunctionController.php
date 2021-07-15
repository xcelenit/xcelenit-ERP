<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Custom\CustomeTransactionUtil;

class CustomeFunctionController extends Controller
{
    //
    public function getCreditNoteValue(Request $request)
    {
       // return $request->all();
         $customTranUtil = new CustomeTransactionUtil();         
         $credit_note = $customTranUtil->getCreditNoteValue($request->contact_id, $request->credit_note_no);
         return $credit_note;     
         
    }
}
