<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Accounting\Entities\DoubleEntryTransactionSchemes;

class CreateDoubleEntryTransactionSchemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_transaction_schemes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('business_id')->unsigned()->index();
            $table->foreign('business_id')->references('id')->on('business');

            $table->string('name',30);
            $table->string('desc',120);
            $table->string('prefix',4)->nullable();
            $table->integer('start_number');

            $table->bigInteger('count');
            $table->tinyInteger('digit');
            $table->integer('year');

            $table->timestamps();
        });


        //Create Default on migate table
        /* 
            Petty Cash Voucher - PCV2020/0001
            Cheque Payment Voucher - CPV2020/0001 - print
            Bank Debit Advisor - BDA2020/0001
            Bank Credit Advisor - BCA2020/0001
            Journal Voucher - JNV2020/0001
        */
        $current_year = date("Y");

        DoubleEntryTransactionSchemes::create(['business_id'=>1, 'name'=>'PCV', 'desc'=>'Petty Cash Voucher', 'prefix'=>'PCV', 'start_number'=>0, 'count'=>0, 'digit'=>4,'year'=>$current_year]);
        DoubleEntryTransactionSchemes::create(['business_id'=>1, 'name'=>'CPV', 'desc'=>'Cheque Payment Voucher', 'prefix'=>'CPV', 'start_number'=>0, 'count'=>0, 'digit'=>4,'year'=>$current_year]);
        DoubleEntryTransactionSchemes::create(['business_id'=>1, 'name'=>'BDA', 'desc'=>'Bank Debit Advisor', 'prefix'=>'BDA', 'start_number'=>0, 'count'=>0, 'digit'=>4,'year'=>$current_year]);
        DoubleEntryTransactionSchemes::create(['business_id'=>1, 'name'=>'BCA', 'desc'=>'Bank Credit Advisor', 'prefix'=>'BCA', 'start_number'=>0, 'count'=>0, 'digit'=>4,'year'=>$current_year]);
        DoubleEntryTransactionSchemes::create(['business_id'=>1, 'name'=>'JNV', 'desc'=>'Journal Voucher', 'prefix'=>'JNV', 'start_number'=>0, 'count'=>0, 'digit'=>4,'year'=>$current_year]);
        
        

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_transaction_schemes');
    }
}
