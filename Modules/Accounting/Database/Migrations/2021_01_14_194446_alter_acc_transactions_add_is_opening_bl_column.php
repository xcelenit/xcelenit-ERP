<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAccTransactionsAddIsOpeningBlColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('acc_transactions', function (Blueprint $table) {
            $table->bigInteger('is_opening_bl')->after('period')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
         //
         Schema::table('acc_transactions', function (Blueprint $table) {
            //
        });
    }
}
