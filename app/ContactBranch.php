<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactBranch extends Model
{
    //
    public static function getBranchesById($contact_id, $with_inactive=false)
    {
        $contact_branches = self::where('vendor_id',$contact_id);

                if($with_inactive==false){
                    $contact_branches->where('is_active',1);
                }

        $contact_branches ->pluck('branch_name','id');
        $contact_branches = $contact_branches->prepend('None', '');    

        return $contact_branches;          

    }
}
