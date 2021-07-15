<?php

namespace Modules\ProductSerial\Http\Controllers;

use DB;
use Menu;
use App\Transaction;
use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Modules\ProductSerial\Entities\ProductSerial;


class DataController extends Controller
{
    /**
     * Defines user permissions for the module. 
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'accounting.access_double_entry_accounting',
                'label' =>  'Access Doble Entry Accounting',
                'default' => false
            ],
        ];
    }
    

    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        
       

        // if ((auth()->user()->can('accounting.access_double_entry_accounting'))) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                        action('\Modules\ProductSerial\Http\Controllers\ProductSerialController@index'),
                        'Product Serial',
                        ['icon' => 'fa fas fa-barcode', 'style' => config('app.env') == 'demo' ? 'background-color: #ff851b;' : '', 'active' => request()->segment(1) == 'productserial']
                    )
                ->order(51);
            });
        // }
    }

    public function checkSerialNo($data)
    {
        //\Log::emergency($data);
        $product_serial = ProductSerial::checkSerial($data['product_id'],$data['serial_no']);
        if($product_serial){
            if($data['location_id']==$product_serial->location_id){
                if($product_serial->status == 0){
                    return '0';
                } else{
                    return '1';
                }
            }else{
                return '4';
            }
            
        }else{
            return 'nf';
        }
    }

    public function issueSerialOnSaveSale($data)
    {
         \Log::emergency($data);

         if(count($data['serial_data'])>0){
            ProductSerial::issueSerial($data['transaction_id'], $data['serial_data']);             
         }

    }
    
     
    
}
