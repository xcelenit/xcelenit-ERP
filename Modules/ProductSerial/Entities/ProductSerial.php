<?php

namespace Modules\ProductSerial\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductSerial extends Model
{
    protected $fillable = ['location_id','product_id','issued_transaction_id','serial_no','status'];


    public static function checkSerial($product_id,$serial_no, $location=null)
    {
        if($location){
            $serial = Self::where('product_id',$product_id)->where('location_id',$location)->where('serial_no',$serial_no)->first();
        }else{
            $serial = Self::where('product_id',$product_id)->where('serial_no',$serial_no)->first();
        }

        return $serial;
    }
    
    public static function restore($serials)
    {
        $serial = Self::whereIn('serial_no',$serials)->update(['status'=>0, 'issued_transaction_id'=>null]);     
        return $serial;
    }

    public static function issueSerial($transaction_id, $serials)
    {
        $serial = Self::whereIn('serial_no',$serials)->update(['status'=>1, 'issued_transaction_id'=>$transaction_id]); 
        return $serial;

    }

    public static function transferSerial($form_location_id, $to_location_id, $serialIds)
    {
        $serial = Self::whereIn('id',$serialIds)
                    ->where('location_id',$form_location_id)
                    ->where('status',0)
                    ->update(['location_id'=>$to_location_id]);
        return $serial;
    }

}
