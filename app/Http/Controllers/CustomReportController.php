<?php

namespace App\Http\Controllers;

use Datatables;
use App\Contact;
use App\Category;
use App\BusinessLocation;
use App\TransactionSellLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomReportController extends Controller
{
    //
    public function costOfSales(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            
        }
        $categories = Category::forDropdown($business_id, 'product');
        $business_locations = BusinessLocation::forDropdown($business_id);
        //$customers = Contact::customersDropdown($business_id);

        return view('reports_custom.cost_of_sales') 
            ->with(compact('business_locations','categories'));
    }

    public function getCostOfSaleData(Request $request)
    {           
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->get('location_id', null);

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date'); 
         
        $vld_str = '';
        $vld_str2 = '';
        if (!empty($location_id)) {
            $vld_str = "AND vld.location_id=$location_id";
            //$vld_str2 = "AND vld.location_id IN ($location_id,1)";

            $vld_str2 = "AND TRNS.location_id= $location_id";
        }

        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $query = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                
               

                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->select(                   
                    'transaction_sell_lines.id',
                    'p.name as product_name',
                    'p.enable_stock',
                    'p.type as product_type',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    't.id as transaction_id',
                    't.transaction_date as transaction_date',
                    DB::raw('DATE_FORMAT(t.transaction_date, "%Y-%m-%d") as formated_date'),
                    DB::raw("(SELECT SUM(vld.qty_available) FROM variation_location_details as vld WHERE vld.variation_id=v.id $vld_str) as current_stock"), //current_stock
                    DB::raw("(SELECT SUM(vld.qty_available) FROM variation_location_details as vld WHERE vld.variation_id=v.id AND vld.location_id = 1) as current_stock_mc"),

                     //[$start_date, $end_date]

                    // DB::raw("(SELECT SUM(TSL.quantity - TSL.quantity_returned) FROM transactions AS TRNS
                    //             JOIN transaction_sell_lines AS TSL ON TRNS.id=TSL.transaction_id
                    //             WHERE TRNS.status='final' AND TRNS.type='sell' $vld_str2 
                    //             AND TSL.variation_id=v.id AND TRNS.transaction_date = $end_date ) as current_stock"),
//

                    DB::raw('SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as total_qty_sold'),
                    'u.short_name as unit',
                        DB::raw('SUM((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal'),

                        DB::raw('SUM((SELECT SUM((TSPL.quantity - TSPL.qty_returned) * PL.purchase_price_inc_tax) FROM transaction_sell_lines_purchase_lines as TSPL 
                                 INNER JOIN purchase_lines as PL ON PL.id = TSPL.purchase_line_id WHERE TSPL.sell_line_id = transaction_sell_lines.id)) as total_cost_as_val')
                        
                )
                 ->groupBy('v.id');
                // ->groupBy('formated_date');
                    

                

            if (!empty($variation_id)) {
                $query->where('transaction_sell_lines.variation_id', $variation_id);
            }
           
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $customer_id = $request->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('t.contact_id', $customer_id);
            }

            $category_id = request()->get('category_id', null);
            if (!empty($category_id)) {
                $query->where('p.category_id', $category_id);
            }
         //   $dataset =$query->get();
          //  Log::emergency($dataset);

           // return null;

            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }

                    return $product_name;
                })
                ->addColumn('current_stock_all',function ($row) use ($location_id) {
                    if ($row->enable_stock) {
                        if (!empty($location_id)) {
                            return  '<span data-is_quantity="true" class="display_currency current_stock_all" data-currency_symbol=false data-orig-value="' . ((float)($row->current_stock_mc+$row->current_stock)) . '" data-unit="' . $row->unit . '" >' . ((float)($row->current_stock_mc+$row->current_stock)) . '</span> ' .$row->unit;
                        }else{
                            return  '<span data-is_quantity="true" class="display_currency current_stock_all" data-currency_symbol=false data-orig-value="' . ((float)$row->current_stock) . '" data-unit="' . $row->unit . '" >' . ((float) $row->current_stock) . '</span> ' .$row->unit;
                        }
                     }else{
                        return '';
                     }
                })
                ->editColumn('total_qty_sold', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency sell_qty" data-currency_symbol=false data-orig-value="' . (float)$row->total_qty_sold . '" data-unit="' . $row->unit . '" >' . (float) $row->total_qty_sold . '</span> ' .$row->unit;
                })
                ->editColumn('current_stock', function ($row) {
                    if ($row->enable_stock) {
                        return '<span data-is_quantity="true" class="display_currency current_stock" data-currency_symbol=false data-orig-value="' . (float)$row->current_stock . '" data-unit="' . $row->unit . '" >' . (float) $row->current_stock . '</span> ' .$row->unit;
                    } else {
                        return '';
                    }
                })
                 ->editColumn('subtotal', function ($row) {
                     return '<span class="display_currency row_subtotal" data-currency_symbol = true data-orig-value="' . $row->subtotal . '">' . number_format($row->subtotal,2,'.',',') . '</span>';
                 })

                 ->editColumn('total_cost_as_val', function ($row) {
                    // total_cost_as_val
                    return '<span class="display_currency row_total_cost_val" data-currency_symbol = true data-orig-value="' . $row->total_cost_as_val . '">' . number_format($row->total_cost_as_val,2,'.',',') . '</span>';
                    //return '<span class="display_currency row_total_cost_val" data-currency_symbol = true data-orig-value="' . $row->get_cost_val() . '">' . $row->get_cost_val() . '</span>';

                    //($row->get_cost_val($row->id))
                })
                ->addColumn('gross_profit', function ($row) {
                    return '<span class="display_currency row_gross_profit float-" data-currency_symbol = true data-orig-value="' .  ($row->subtotal-$row->total_cost_as_val) . '">' . number_format($row->subtotal-$row->total_cost_as_val,2,'.',',') . '</span>';
                    //($row->get_cost_val($row->id))
                })
                ->addColumn('gross_profit_perc', function ($row) {
                    if($row->subtotal>0){
                        
                       return '<span class="row_gross_profit_perc float-" data-orig-value="' .  (($row->subtotal - $row->total_cost_as_val)/$row->subtotal * 100.0) . '">' . number_format(((($row->subtotal - $row->total_cost_as_val)/$row->subtotal) * 100.0),2,'.',',') . '%</span>';
                    
                    }else{
                        return '<span class="row_gross_profit_perc float-" data-orig-value="' .  0.00 . '">' . 0.00 . '%</span>';
                    }
                   
                    //($row->get_cost_val($row->id)) 
                })
                
                ->rawColumns(['current_stock', 'subtotal', 'total_qty_sold','total_cost_as_val','current_stock_all','gross_profit','gross_profit_perc'])
                ->make(true);
        }
    }
}
