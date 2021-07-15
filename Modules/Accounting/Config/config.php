<?php

return [

    'name' => 'Accounting',
    'module_version' => '1.5',

    'print_busines_name'=>'BETA XCELEN IT',

    'link_transaction' =>[
        'sales'=>true, //Sale , Receipt
        'purchases'=>true, //GRN
        'expenses'=>true,
        'credit_note'=>true,

        'debit_note'=>true,
        'stock_transafers'=>false,
        'stock_adjustment'=>false,
        'stock_opening_bl'=>false,         
    ],

    'document_type' => [

            ['type'=>'Sales','code'=>'SAL'],
            ['type'=>'Cost of Sale','code'=>'COS'],
            
            ['type'=>'Cost of Sale Return','code'=>'COSR'],
            ['type'=>'Credit Note Payment','code'=>'CNP'],

            ['type'=>'GRN','code'=>'GRN'],
            ['type'=>'Expenses','code'=>'EXP'],

            ['type'=>'Credit Note','code'=>'CDN'],
            ['type'=>'Debit Note','code'=>'DBN'],
            ['type'=>'Receipt','code'=>'RCP'],
            
            ['type'=>'Petty Cash Voucher','code'=>'PCV'],
            ['type'=>'Cheque Payment Voucher','code'=>'CPV'],
            ['type'=>'Bank Debit Advisor','code'=>'BDA'],
            ['type'=>'Bank Credit Advisor','code'=>'BCA'],
            ['type'=>'Journal Voucher','code'=>'JNV'],
            ['type'=>'Cheque Return Note','code'=>'CRN'],
            ['type'=>'Bank Deposit','code'=>'BDS'],

        ],

        
    'document_type_prefix'=>[

        'sales'=>'SAL',
        'grn'=>'GRN',
        'expenses'=>'EXP',
        
        'receipt'=>'RCP',

        'credit_note'=>'CDN',
        'credit_note_payment'=>'CNP',
        'debit_note'=>'DBN',
        'debit_note_payment'=>'DNP',



        'petty_cash_vc'=>'PCV',
        'cheque_payment_vc'=>'CPV',
        'bank_debit_ad'=>'BDA',
        'bank_credit_ad'=>'BCA',
        'journal_vo'=>'JNV',

        'cheque_return_note'=>'CRN',
        'bank_deposit'=>'BDS',
        'bank_transfer_note'=>'BTN',
         


        
    ],
    
    'editable_doc_type_array'=>['RCP','PCV','CPV','BDA','BCA','JNV','CRN','BDN','BTN'],

    

    'default_categories'=>[
        'non_current_asset'=>1,
        'current_asset'=>2,
    ],

    'default_account_type'=>[
        'assets'=>1,
        'expenses'=>2,
        'liabilities'=>3,
        'equity'=>4,
        'income'=>5,

    ],

    'default_account_ids'=>[
        'debtor' => 2,
        'creditor' => 9,
        'sales' => 11,
        'inventory' => 3,

        'cash_in_hand' => 4,
        'cheque_in_hand' => 5,
        'card_payment' => 6,
        'other_payment' => null,

        'cost_of_sale' => 8,
    ],

    'petty_cash_account_ids' =>[7],
    'default_in_hand_account_ids' =>[4,5,6,7],

    'direct_payment_asset_account_id' =>100,

    'payment_methods' =>[
            ['method'=>'none','name'=>'NONE', 'default_account_id'=>null],
            ['method'=>'cash','name'=>'CASH', 'default_account_id'=>4],
            ['method'=>'card','name'=>'CARD', 'default_account_id'=>6],
            ['method'=>'cheque','name'=>'CHEQUE', 'default_account_id' =>5],
            ['method'=>'bank deposit','name'=>'BANK DEPOSIT', 'default_account_id'=>null],
    ],
    
    'expen'

];

/*

Petty Cash Voucher - PCV2020/0001
Cheque Payment Voucher - CPV2020/0001 - print
Bank Debit Advisor - BDA2020/0001
Bank Credit Advisor - BCA2020/0001
Journal Voucher - JNV2020/0001





Assts
->Non Current Asset 
    -> Fixed Assets
->Current Asset     
    -> Debtor Control Account
    -> Inventory
    -> Cash In Hand
    -> Cheque In Hand
    -> Card Payment
    -> Petty Cash
    
----------------------------
Expences
-> Operational Expences
    -> Cost Of Sales
    -> Salary

----------------------------
Libility
-> Non Current Liability    
-> Current Liability
    -> Creditor Control Account
 ----------------------------  
 Equity
-> Owner’s equity
    -> Started Capital
    -> Retained Earnings


--------------------
Income
-> Operating Revenue
    -> Sales
-> Non Operating Revenue
---------------------------- 



     'accounts' => [
        //Balance Sheet: Assets Accounts
        Account::NON_CURRENT_ASSET => 'Non Current Asset',
        Account::CONTRA_ASSET => 'Contra Asset',
        Account::INVENTORY => 'Inventory',
        Account::BANK => 'Bank',
        Account::CURRENT_ASSET => 'Current Asset',
        Account::RECEIVABLE => 'Receivable',

        //Balance Sheet: Liabilities Accounts
        Account::NON_CURRENT_LIABILITY => 'Non Current Liability',
        Account::CONTROL_ACCOUNT => 'Control Account',
        Account::CURRENT_LIABILITY => 'Current Liability',
        Account::PAYABLE => 'Payable',
        Account::RECONCILIATION => 'Reconciliation',

        //Balance Sheet: Equity Accounts
        Account::EQUITY => 'Equity',

        //Income Statement: Operations Accounts
        Account::OPERATING_REVENUE => 'Operating Revenue',
        Account::OPERATING_EXPENSE => 'Operating Expense',

        //Income Statement: Non Operations Accounts
        Account::NON_OPERATING_REVENUE => 'Non Operating Revenue',
        Account::DIRECT_EXPENSE => 'Direct Expense',
        Account::OVERHEAD_EXPENSE => 'Overhead Expense',
        Account::OTHER_EXPENSE => 'Other Expense',
    ],
*/