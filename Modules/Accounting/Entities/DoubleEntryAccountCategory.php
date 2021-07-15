<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryAccountType;

class DoubleEntryAccountCategory extends Model 
{
    protected $fillable = ['business_id','account_type_id','category_code','category_name','sort_order','is_default'];
    protected $table='acc_account_categories';

    public function account_type()
    {
        return $this->belongsTo(DoubleEntryAccountType::class, 'account_type_id');
    }

    public static function getDropDown($type = null)
    {   
        if($type!=null){
            return DoubleEntryAccountCategory::where('account_type_id',$type)->pluck('category_name', 'id');
        }else{
            return DoubleEntryAccountCategory::pluck('category_name', 'id');
        }
        
    }
    

    

}
