<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoubleEntryLedgerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_ledger_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('transaction_id')->unsigned()->index();
            $table->foreign('transaction_id')->references('id')->on('acc_transactions');

            $table->bigInteger('account_id')->unsigned()->index();
            $table->foreign('account_id')->references('id')->on('acc_accounts');

            $table->string('entry_type',2);
            $table->double('amount',24,2);
            $table->boolean('is_reconcile')->nullable();
            
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
        Schema::dropIfExists('acc_ledger_transactions');
    }
}
