<?php

namespace Modules\Accounting\Http\Controllers;

use App\BusinessLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryLedgerTransaction;

class DoubleEntryAccountReportController extends Controller
{
    public function trialBalance()
    {   

        $table_data =   $this->getTrialBalanceData(date('Y-m-d'));
       // return $table_data;
        return view('accounting::reports.trial_balance',compact('table_data'));
 
    }

    public function trialBalanceData(Request $request)
    {   
        
        //return $request->all();
         
        $rp_date = date('Y-m-d',strtotime($request->rp_date));
         return $this->getTrialBalanceData($rp_date);
    }

    public function getTrialBalanceData($date_of_report=null)
    {   
        //return $date_of_report;
            $accounts_data = DoubleEntryAccount::where('acc_accounts.is_active',1)
                        ->join('acc_account_types as AT','AT.id','=','acc_accounts.account_type_id')
                        ->join('acc_account_categories as AC','AC.id','=','acc_accounts.category_id');

                        if($date_of_report==null){
                            $accounts_data->select(['acc_accounts.*','AT.type','AT.id as tr_type_id','AT.trs_type','AC.category_name','AC.category_code',
                                DB::raw('(SELECT SUM(IF(ALE.entry_type=IF((tr_type_id <= 2), "DR", "CR"), ALE.amount, -1 * ALE.amount)) FROM acc_ledger_transactions AS ALE 
                                INNER JOIN acc_transactions AS ATS ON ALE.transaction_id = ATS.id WHERE ALE.account_id = acc_accounts.id 
                                AND ATS.is_canceled=0 ) as account_balance')]
                            );
                        }else{
                            $accounts_data->select(['acc_accounts.*','AT.type','AT.id as tr_type_id' ,'AT.trs_type','AC.category_name','AC.category_code',
                                DB::raw('(SELECT SUM(IF(ALE.entry_type=IF((tr_type_id <= 2), "DR", "CR"), ALE.amount, -1 * ALE.amount)) FROM acc_ledger_transactions AS ALE 
                                INNER JOIN acc_transactions AS ATS ON ALE.transaction_id = ATS.id WHERE date(ATS.transaction_date) <= "'.$date_of_report.'" AND ALE.account_id = acc_accounts.id 
                                AND ATS.is_canceled=0 ) as account_balance')]
                            );
                        }
                        
                        // ->groupBy('acc_accounts.account_type_id','acc_accounts.category_id')   
                     $accounts_all = $accounts_data->orderBy('account_code','ASC')
                                    ->orderBy('AT.id','ASC')
                                    ->get();
                
           // dd($accounts);
           $accounts_all = $accounts_all->groupBy('type');
        //    $accounts_all = $accounts_all->groupBy('category_name');  
            // return $accounts_all;
        $table_html='';

        $total_debit =0;
        $total_credit =0;


           foreach($accounts_all as $type => $accounts){

                 $accounts = $accounts->groupBy('category_name');
                 $table_html .='<tr class="acc-type">
                                    <th colspan="6">'.strtoupper($type).'</th>
                                </tr> ';
                 foreach($accounts as $category => $account){
                        $table_html .='<tr class="acc-category">
                                            <td colspan="1" class="text-center"> - </td>
                                            <th colspan="5">* <i>'.$category.'</i></th>
                                        </tr>';

                      foreach($account as $acc){
                        $table_html .='<tr>
                                            <td colspan="2"></td>
                                            <td>'.$acc->account_code.'</td>
                                            <td>'.$acc->account_name.' - ('.$acc->account_no.')</td>
                                            <td class="text-right"><b>'.((($acc->trs_type=='DEBIT') ? number_format($acc->account_balance,2,'.',',') : null)).'</b></td>
                                            <td class="text-right"><b>'.((($acc->trs_type=='CREDIT') ? number_format($acc->account_balance,2,'.',',') : null)).'</b></td>
                                        </tr>';
                        $total_debit +=((($acc->trs_type=='DEBIT') ? number_format($acc->account_balance,2,'.','') : 0));
                        $total_credit +=((($acc->trs_type=='CREDIT') ? number_format($acc->account_balance,2,'.','') : 0));
                      }
                 }
           }

           $table_html .='<tr class="bg-danger balance">
                            <th colspan="4" class="text-right"><h4 class="bold-font">BALANCE :</h4></th>
                            <th class="text-right"><h4 class="bold-font">'.number_format($total_debit,2,'.',',').'</h4></th>
                            <th class="text-right"><h4 class="bold-font">'.number_format($total_credit,2,'.',',').'</h4></th>
                        </tr>';

            return $table_html;
            
    }

    public function pnlReport()
    {   
       $business_id = request()->session()->get('user.business_id');
       $table_data =  $this->getPnlReportData('2020-04-01', '2021-03-31');
       $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('accounting::reports.pnl_report',compact('table_data','business_locations'));
    }

    public function pnlData(Request $request)
    {  
         $start_date = date('Y-m-d', strtotime($request->start));
         $end_date = date('Y-m-d',strtotime($request->end));
         
         return $this->getPnlReportData($start_date, $end_date);
    }

    public function getPnlReportData($start_date, $end_date)
    {   
        
         
        $business_id = request()->session()->get('user.business_id');
        $location_wise_data = BusinessLocation::where('business_id',$business_id)->where('is_active',1)->select('id','name')->get();
         
        $html_data='';

        $html_line_br=' <tr><td colspan="4">-</td></tr>';

        $sales_account = DoubleEntryAccount::find(config('accounting.default_account_ids.sales')); 
        $sales_account->account_balance =  $sales_account->getBalanceDateRange($start_date, $end_date);
        //sales row 
        // $html_data .= ' <tr class="acc-type">
        //                     <th colspan="3">SALES REVENUE</th>
        //                     <th class="text-right" >'.number_format($sales_account->account_balance,2,'.',',').'</th>
        //                     <th></th>
        //                 </tr>';
        
        //Location wise sales row       
        $html_sale_lc_wise='';
        foreach($location_wise_data as $location){
            $val = $sales_account->getBalanceDateRange($start_date, $end_date,$location->id);

            $html_sale_lc_wise .='<tr>
                                     <td>-</td>
                                     <td>'.$location->name.'</td>
                                     <td class="text-right" >'.number_format($val,2,'.',',').'</td>
                                     <td></td>
                                   </tr> ';
        }

        // $html_data .=$html_sale_lc_wise;


         //Cost of Sale row 
        $cost_of_sales_account = DoubleEntryAccount::find(config('accounting.default_account_ids.cost_of_sale')); 
        $cost_of_sales_account->account_balance =  $cost_of_sales_account->getBalanceDateRange($start_date, $end_date);
        // $html_data .= ' <tr class="acc-type">
        //                     <th colspan="3">COST OF SALES</th>
        //                     <th class="text-right" >'.number_format($cost_of_sales_account->account_balance,2,'.',',').'</th>                            
        //                 </tr>';
        
        //Location wise Cost of sales row       
        $html_cos_sale_lc_wise='';
        $html_gp_lc_wise ='<tr>
                                <td>-</td>                                
                                <th>
                                    <div class="row">
                                        <div class="col-sm-6 text-left">Location</div>
                                        <div class="col-sm-3 text-right"> Sales  </div>
                                        <div class="col-sm-3 text-right"> Cost of Sales </div>
                                    </div>
                                </th>
                                <th class="text-center" >Gross Profit</th>
                                <td></td>
                                <th></th>
                                
                            </tr> ';
        $tot_cost_of_sale =0;
        $tot_sale =0;
        foreach($location_wise_data as $location){
            $cost_val = $cost_of_sales_account->getBalanceDateRange($start_date, $end_date,$location->id);
            $sale_val = $sales_account->getBalanceDateRange($start_date, $end_date,$location->id);
            
            $tot_cost_of_sale +=$cost_val;
            $tot_sale += $sale_val;
            
            $html_cos_sale_lc_wise .='<tr>
                                     <td>-</td>
                                     <td>'.$location->name.'</td>
                                     <td class="text-right" >'.number_format($cost_val,2,'.',',').'</td>
                                     <td></td>
                                     
                                   </tr> ';

            //number_format(((($row->subtotal - $row->total_cost_as_val)/$row->subtotal) * 100.0),2,'.',',')  
            //<td>'.$location->name.'  [ '.number_format($sale_val,2,'.',',').' - '.number_format($cost_val,2,'.',',').' ]</td>
            $gp_percentage = $sale_val > 0 ? number_format(((($sale_val - $cost_val)/$sale_val) * 100.0),2,'.',',') : 0.00;
            $html_gp_lc_wise .='<tr>
                                <td>-</td>
                                
                                <td>
                                    <div class="row">
                                        <div class="col-sm-6 text-left">'.$location->name.'</div>
                                        <div class="col-sm-3 text-right"> '.number_format($sale_val,2,'.',',').'  </div>
                                        <div class="col-sm-3 text-right"> '.number_format($cost_val,2,'.',',').'  </div>
                                    </div>
                                </td>
                                <td class="text-right" >'.number_format(($sale_val-$cost_val),2,'.',',').'</td>
                                <td></td>
                                <td class="text-center">'.$gp_percentage.'%</td>
                                
                            </tr> ';
        }

        // $html_data .=$html_cos_sale_lc_wise;


          //Gross Profit row 
           
          $gross_profit =  $sales_account->account_balance - $cost_of_sales_account->account_balance; 
          $total_gp_percentage = $sales_account->account_balance > 0 ? number_format(($gross_profit/$sales_account->account_balance * 100.00),2,'.',',') : 0.00;
          $html_data .=$html_line_br;
          $html_data .= ' <tr class="acc-type">
                              <td colspan="3"><b>GROSS PROFIT</b> &nbsp; &nbsp; <i>[ Sales - Cost of Sales ]</i></td>
                              <th class="text-right" >'.number_format($gross_profit,2,'.',',').'</th>
                              <th class="text-center" >'.$total_gp_percentage.'%</th>
                          </tr>';
        
         $html_data .= $html_gp_lc_wise;

         $html_data .= ' <tr class="acc-type">
         <td ><b>TOTAL</b> </td>
         <th> 
            <div class="row">
                <div class="col-sm-6 text-left"></div>
                <div class="col-sm-3 text-right"> '.number_format($tot_sale,2,'.',',').'  </div>
                <div class="col-sm-3 text-right"> '.number_format($tot_cost_of_sale,2,'.',',').'  </div>
            </div>
         </th>
         <th class="text-right" >'.number_format($gross_profit,2,'.',',').'</th>
         <th></th>
         <th class="text-center" >'.$total_gp_percentage.'%</th>
     </tr>';

        //OPERATIONAL EXP
         $op_expencess_accounts = DoubleEntryAccount::where('is_active',1)->where('category_id',3)->where('account_type_id',2)
                                                    ->whereNotIn('id',[config('accounting.default_account_ids.cost_of_sale')])->get();
         $op_expencess_accounts_html = '';
         $op_expencess_accounts_total=0;
                  
         foreach($op_expencess_accounts as $exp_acc){

            $balance = $exp_acc->getBalanceDateRange($start_date, $end_date);            
            $op_expencess_accounts_total += $balance;

            $op_expencess_accounts_html .='<tr >
                                                <td>-</td>
                                                <td>'.$exp_acc->account_name.'</td>
                                                <td class="text-right" >'.number_format($balance,2,'.',',').'</td>
                                                <td></td>
                                            </tr> ';
         }

         $html_data .=$html_line_br;
         $html_data .= '<tr class="acc-type">
                                            <th colspan="3">Operating Expenses </th> 
                                            <th class="text-right">'.number_format($op_expencess_accounts_total,2,'.',',').'</th>  
                                                                      
                                        </tr>';
        
        $html_data .=$op_expencess_accounts_html;


        //NON OPERATIONAL EXP
        $nop_expencess_accounts = DoubleEntryAccount::where('is_active',1)->where('category_id',9)->where('account_type_id',2)
                                                    // ->whereNotIn('id',[config('accounting.default_account_ids.cost_of_sale')])
                                                    ->get();
         $nop_expencess_accounts_html = '';
         $nop_expencess_accounts_total=0;
                  
         foreach($nop_expencess_accounts as $exp_acc){

            $balance = $exp_acc->getBalanceDateRange($start_date, $end_date);            
            $nop_expencess_accounts_total += $balance;

            $nop_expencess_accounts_html .='<tr >
                                                <td>-</td>
                                                <td>'.$exp_acc->account_name.'</td>
                                                <td class="text-right" >'.number_format($balance,2,'.',',').'</td>
                                                <td></td>
                                            </tr> ';
         }
         
         $html_data .=$html_line_br;
         $html_data .= '<tr class="acc-type">
                                            <th colspan="3">Non Operating Expenses </th> 
                                            <th class="text-right">'.number_format($nop_expencess_accounts_total,2,'.',',').'</th>                            
                                        </tr>';
        
        $html_data .=$nop_expencess_accounts_html;


         //TOTAL EXP
        $total_expencess =  $op_expencess_accounts_total + $nop_expencess_accounts_total; 
        $html_data .=$html_line_br;
        $html_data .= ' <tr class="acc-type">
                            <td colspan="3"><b>TOTAL EXPENSES</b> &nbsp; &nbsp; <i>[ Operating Expenses + Non Operating Expenses ]</i></td>
                            <th class="text-right" >'.number_format($total_expencess,2,'.',',').'</th>
                        </tr>';
        //$html_data .=$html_line_br;

        //TOTAL INCOME FORM OPERATION
        $total_income_form_op =  $gross_profit - $total_expencess; 
        $html_data .=$html_line_br;
        $html_data .= ' <tr class="acc-type">
                            <td colspan="3"><b>INCOME FORM OPERATION</b>  &nbsp; &nbsp; <i>[ Gross Profit - Total Expenses ]</i> </td> 
                            <th class="text-right" >'.number_format($total_income_form_op,2,'.',',').'</th>
                        </tr>';
        //$html_data .=$html_line_br;

        //OTHER INCOME
        $other_income_accounts = DoubleEntryAccount::where('is_active',1)->where('category_id',7)->where('account_type_id',5)
                                                    ->whereNotIn('id',[config('accounting.default_account_ids.sales')])
                                                    ->get();
        $other_income_accounts_html = '';
        $other_income_accounts_total=0;

        foreach($other_income_accounts as $exp_acc){

        $balance = $exp_acc->getBalanceDateRange($start_date, $end_date);            
        $other_income_accounts_total += $balance;

        $other_income_accounts_html .='<tr >
            <td>-</td>
            <td>'.$exp_acc->account_name.'</td>
            <td class="text-right" >'.number_format($balance,2,'.',',').'</td>
            <td></td>
        </tr> ';
        }

        // $html_data .=$html_line_br;
        $html_data .= '<tr class="acc-type">
        <th colspan="3">OTHER INCOME </th> 
        <th class="text-right">'.number_format($other_income_accounts_total,2,'.',',').'</th>                            
        </tr>';

        $html_data .=$other_income_accounts_html;


        //NET PROFIT 
        $net_profit =  $total_income_form_op + $other_income_accounts_total; 
        $html_data .=$html_line_br;
        $html_data .= ' <tr class="acc-type">
                            <td colspan="3"><b>NET PROFIT </b>  &nbsp; &nbsp; <i>[ Income Form Operation + Other Income ]</i> </td>  
                            <th class="text-right" >'.number_format($net_profit,2,'.',',').'</th>
                        </tr>';

        return $html_data;
                       
    }
 
}
