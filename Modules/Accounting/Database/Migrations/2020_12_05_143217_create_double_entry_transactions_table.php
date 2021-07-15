<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoubleEntryTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->integer('business_id')->unsigned()->index();
            $table->foreign('business_id')->references('id')->on('business');

            $table->integer('location_id')->unsigned()->index()->nullable();
            $table->foreign('location_id')->references('id')->on('business_locations');
           
            $table->integer('added_by')->unsigned()->index();
            $table->foreign('added_by')->references('id')->on('users');


            $table->integer('vendor_id')->unsigned()->index()->nullable();
            $table->foreign('vendor_id')->references('id')->on('contacts');

            
            $table->string('document_no',30)->nullable();
            $table->string('document_type',12);
            $table->dateTime('transaction_date');

            $table->string('payment_method',10)->nullable();
            $table->string('payment_status',7)->nullable();

            $table->double('total_amount',24,2);
            $table->double('total_unaj_amount',24,2)->default(0.00);
            
            

            $table->string('cheque_no',50)->nullable();
            $table->string('payee',120)->nullable();
            $table->date('cheque_date')->nullable();
            
            
            
            $table->string('payment_note',255)->nullable();

            $table->bigInteger('perent_transaction_id')->unsigned()->index()->nullable();
            $table->foreign('perent_transaction_id')->references('id')->on('acc_transactions');

            $table->bigInteger('deposit_transaction_id')->unsigned()->index()->nullable();
            $table->foreign('deposit_transaction_id')->references('id')->on('acc_transactions');

            $table->integer('period');
            

            $table->boolean('is_rec')->nullable();
            $table->boolean('is_canceled');
            $table->boolean('is_print')->nullable();
            $table->boolean('is_print_chq')->nullable();
            $table->tinyInteger('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_transactions');
    }
}
