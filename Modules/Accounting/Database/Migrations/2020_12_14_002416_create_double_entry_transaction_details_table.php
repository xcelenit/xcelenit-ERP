<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoubleEntryTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_transaction_details', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('transaction_id')->unsigned()->index();
            $table->foreign('transaction_id')->references('id')->on('acc_transactions');

            $table->bigInteger('perent_transaction_id')->unsigned()->index()->nullable();
            $table->foreign('perent_transaction_id')->references('id')->on('acc_transactions');
            
            $table->string('ref_no',30)->nullable();
            $table->double('sub_amount',24,2)->nullable();
            $table->string('desc',120)->nullable();

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
        Schema::dropIfExists('acc_transaction_details');
    }
}
