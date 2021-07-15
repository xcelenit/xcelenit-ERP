<?php

namespace Modules\Accounting\Http\Controllers;

 
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Modules\Accounting\Entities\Utility\Utility;
use Modules\Accounting\Entities\DoubleEntryTransaction;

class DoubleEntryAccountDocumentController extends Controller
{  

   protected $utility;
   


   public function __construct(Utility $utility) {
       $this->utility = $utility;
      
   } 

     public function printPaymentVoucher($transaction_id)
     {
        $transaction = DoubleEntryTransaction::where('id',$transaction_id)
                      ->with('vendor','transactionDetails')                      
                      ->first();

        // return $transaction;
         

        $busines_name = config('accounting.print_busines_name');
        $total_in_word = $this->utility->numToWord($transaction->total_amount);
      
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('accounting::documents.payment_voucher',compact('transaction','busines_name','total_in_word'));        
        $pdf->setPaper('a4', 'potrate');
        return $pdf->stream("dompdf_out.pdf", array("Attachment" => false));
     }

     public function printCheque($transaction_id)
     {
         
         $transaction = DoubleEntryTransaction::where('id',$transaction_id)
               ->with('vendor','transactionDetails')                      
               ->first();

               if($transaction->is_print_chq==1){
                 // return back();
               }
          
         $transaction->is_print_chq =1;
         $transaction->save();

         //$busines_name = config('accounting.print_busines_name');
         $total_in_word = $this->utility->numToWord($transaction->total_amount);

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('accounting::documents.cheque',compact('transaction','total_in_word'));
        $pdf->setPaper(array(0, 0,708.1,252), 'portrait');
        return $pdf->stream("dompdf_out.pdf");
     }
}
