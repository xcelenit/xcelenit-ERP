<?php

use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\App;

/*
|--------------------------------------------------------------------------
| Web Routes 
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['middleware' => ['web', 'authh', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu'], 'prefix' => 'accounting'], function () {
    
    
    //Dashboard
    Route::get('/', 'AccountingController@index');
    Route::post('/dashboard/getdata', 'AccountingController@getDashboardData');

    //Master Data
    Route::get('/master-data', 'AccountingController@masterData');
    Route::post('/master-data/store-account-types', 'DoubleEntryAccountCategoryController@store');    
    Route::get('/master-data/get-account-types', 'DoubleEntryAccountCategoryController@getData');

    //Accounts
    Route::get('/accounts', 'DoubleEntryAccountController@index');
    Route::get('/account/create', 'DoubleEntryAccountController@create');
    Route::post('/account/categories-by-type', 'DoubleEntryAccountCategoryController@getCategories');

    

    Route::post('/account/store', 'DoubleEntryAccountController@store');
    Route::get('/account/get', 'DoubleEntryAccountController@getData');

    Route::get('/control-accounts/{type}', 'AccountingController@controlACIndex');
    Route::post('/control-accounts/getdata', 'AccountingController@getControlACData');
    Route::post('/control-accounts/summary', 'AccountingController@getControlAccountSummary');


    //Receipt payment
    Route::get('/payment/receipt/add', 'DoubleEntryAccountPaymentController@addReceipt');
    Route::get('/payment/receipt/{id}/edit', 'DoubleEntryAccountPaymentController@editReceipt');

    Route::post('/payment/debtor/get-due-invoice', 'DoubleEntryAccountPaymentController@getDebtorDueInvoice');
    Route::post('/payment/debtor/store', 'DoubleEntryAccountPaymentController@storeDebtorPayment');

    //Add Payment Voucher
    Route::get('/payment/petty-cash-voucher/add', 'DoubleEntryAccountPaymentController@addPettyCashVoucher');
    Route::get('/payment/cheque-payment-voucher/add', 'DoubleEntryAccountPaymentController@addChequeVoucher');
    Route::get('/payment/cheque-payment-voucher/{id}/edit', 'DoubleEntryAccountPaymentController@editChequeVoucher');

    Route::get('/journal-entry/add', 'DoubleEntryAccountPaymentController@addJournalEntry');

    Route::post('/payment/creditor/get-due-invoice', 'DoubleEntryAccountPaymentController@getCreditorDueInvoice');
    Route::post('/payment/creditor/store', 'DoubleEntryAccountPaymentController@storeCreditorPayment');
    Route::post('/journal-entry/store', 'DoubleEntryAccountPaymentController@storeJornalVoucher');    
    Route::post('/payment/get/creditor/payee', 'DoubleEntryAccountPaymentController@getPyeeByVendor');

    //transactions
    Route::get('/transactions', 'DoubleEntryAccountTransactionController@index');
    Route::post('/transactions/get', 'DoubleEntryAccountTransactionController@getData');


    //Reports
    Route::get('/report/trial-balance','DoubleEntryAccountReportController@trialBalance');
    Route::post('/report/trial-balance/data','DoubleEntryAccountReportController@trialBalanceData');

    Route::get('/report/pnl','DoubleEntryAccountReportController@pnlReport');
    Route::post('/report/pnl/data','DoubleEntryAccountReportController@pnlData');


    //Printing Document
    Route::get('/documents/payment-voucher/{transaction_id}/print','DoubleEntryAccountDocumentController@printPaymentVoucher');
    Route::get('/documents/cheque/{transaction_id}/print','DoubleEntryAccountDocumentController@printCheque');


    //Ledger
    Route::get('/ledger','AccountingController@ledger');
    Route::post('/ledger/data','AccountingController@getledgerData');
    Route::post('/ledger/data/summary','AccountingController@getledgerSummary');

    //install
    Route::get('/install', 'InstallController@index');
    Route::post('/install', 'InstallController@install');
    Route::get('/install/uninstall', 'InstallController@uninstall');
    Route::get('/install/update', 'InstallController@update');


    //Migrate Data
    Route::get('/migrate/sales','DataMigrateController@selesMigrate');
    Route::get('/migrate/purchases','DataMigrateController@purchasesMigrate');
    Route::get('/migrate/expenses','DataMigrateController@expensesMigrate');
    Route::get('/test','DataMigrateController@test');
    
    //Bank Deposit
    Route::get('/bank/deposit','DoubleEntryBankDepositController@index');
    Route::post('/bank/deposit/store','DoubleEntryBankDepositController@store');
    Route::post('/bank/deposit/get/data','DoubleEntryBankDepositController@getData');


    //Bank Reconcilation
    Route::get('/bank/reconcilation','BankReconcilationController@index');
    Route::post('/bank/reconcilation/getrecdata','BankReconcilationController@getRecData');
    Route::post('/bank/reconcilation/store','BankReconcilationController@store');


    Route::get('/pdf', function () {

        // $pdf = App::make('dompdf.wrapper');
        // $pdf->loadHTML('<h1>Test</h1>');
        // return $pdf->stream();
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML('<h1>Test</h1>');
        $pdf->setPaper('a5', 'landscape');
        // $customPaper = array(0,0,360,360);
        // $pdf->set_paper($customPaper,'landscape');
        return $pdf->stream("dompdf_out.pdf",array("Attachment" => false));
         
    });
    

    

    



});