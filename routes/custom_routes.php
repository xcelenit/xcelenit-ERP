<?php

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])->group(function () {


    Route::get('/reports/cost-of-sales','CustomReportController@costOfSales');
    Route::get('/reports/cost-of-sales/get-data','CustomReportController@getCostOfSaleData');

    Route::post('/get/credit-note/value','CustomeFunctionController@getCreditNoteValue');
});