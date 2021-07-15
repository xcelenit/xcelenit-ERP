<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Entities\DoubleEntryAccount;
use Modules\Accounting\Entities\DoubleEntryAccountType;
use Modules\Accounting\Entities\DoubleEntryAccountCategory;

class AccountingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
                
        //Default Data
        DoubleEntryAccountType::create(['type_code'=>'100','type'=>'Assets','trs_type'=>'DEBIT']);
        DoubleEntryAccountType::create(['type_code'=>'200','type'=>'Expenses','trs_type'=>'DEBIT']);
        DoubleEntryAccountType::create(['type_code'=>'300','type'=>'Liabilities','trs_type'=>'CREDIT']);
        DoubleEntryAccountType::create(['type_code'=>'400','type'=>'Equity','trs_type'=>'CREDIT']);
        DoubleEntryAccountType::create(['type_code'=>'500','type'=>'Income','trs_type'=>'CREDIT']);

        //Default Account Categories
        DoubleEntryAccountCategory::create(['business_id'=>1,'account_type_id'=>1,'category_code'=>'01','category_name'=>'NON CURRENT ASSETS','sort_order'=>1,'is_default'=>1]);
        DoubleEntryAccountCategory::create(['business_id'=>1,'account_type_id'=>1,'category_code'=>'02','category_name'=>'CURRENT ASSETS','sort_order'=>1,'is_default'=>1]);
        DoubleEntryAccountCategory::create(['business_id'=>1,'account_type_id'=>2,'category_code'=>'01','category_name'=>'OPERATIONAL EXPENCES','sort_order'=>1,'is_default'=>1]);
        DoubleEntryAccountCategory::create(['business_id'=>1,'account_type_id'=>3,'category_code'=>'01','category_name'=>'NON CURRENT LIABILITY','sort_order'=>1,'is_default'=>1]);
        DoubleEntryAccountCategory::create(['business_id'=>1,'account_type_id'=>3,'category_code'=>'02','category_name'=>'CURRENT LIABILITY','sort_order'=>1,'is_default'=>1]);
        DoubleEntryAccountCategory::create(['business_id'=>1,'account_type_id'=>4,'category_code'=>'01','category_name'=>'OWNERS EQUITY','sort_order'=>1,'is_default'=>1]);
        DoubleEntryAccountCategory::create(['business_id'=>1,'account_type_id'=>5,'category_code'=>'01','category_name'=>'OPERATING REVENUE','sort_order'=>1,'is_default'=>1]);
        DoubleEntryAccountCategory::create(['business_id'=>1,'account_type_id'=>5,'category_code'=>'02','category_name'=>'NON OPERATIONG REVENUE','sort_order'=>1,'is_default'=>1]);

        
        //Default Account
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>1,'category_id'=>1,'created_by'=>1,'account_code'=>'100-01-0001','account_name'=>'Fixed Assets','account_no'=>'NCA00001','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>1,'category_id'=>2,'created_by'=>1,'account_code'=>'100-02-0001','account_name'=>'Debtor Control Account','account_no'=>'CAA00001','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>1,'category_id'=>2,'created_by'=>1,'account_code'=>'100-01-0002','account_name'=>'Inventory','account_no'=>'CAA00002','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>1,'category_id'=>2,'created_by'=>1,'account_code'=>'100-01-0003','account_name'=>'Cash In Hand','account_no'=>'CAA00003','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>1,'category_id'=>2,'created_by'=>1,'account_code'=>'100-01-0004','account_name'=>'Cheque In Hand','account_no'=>'CAA00004','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>1,'category_id'=>2,'created_by'=>1,'account_code'=>'100-01-0005','account_name'=>'Card Payment','account_no'=>'CAA00005','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>1,'category_id'=>2,'created_by'=>1,'account_code'=>'100-01-0006','account_name'=>'Petty Cash','account_no'=>'CAA00006','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>2,'category_id'=>3,'created_by'=>1,'account_code'=>'200-01-0001','account_name'=>'Cost Of Sales','account_no'=>'OEA00001','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>3,'category_id'=>5,'created_by'=>1,'account_code'=>'300-02-0001','account_name'=>'Creditor Control Account','account_no'=>'CLA00001','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>4,'category_id'=>6,'created_by'=>1,'account_code'=>'400-01-0001','account_name'=>'Capital','account_no'=>'CPA00001','balance'=>0,'is_active'=>1,'is_default'=>1]);
        DoubleEntryAccount::create(['business_id'=>1,'account_type_id'=>5,'category_id'=>7,'created_by'=>1,'account_code'=>'500-01-0001','account_name'=>'Sales','account_no'=>'ORA00001','balance'=>0,'is_active'=>1,'is_default'=>1]);
                






        









        
    }
}
