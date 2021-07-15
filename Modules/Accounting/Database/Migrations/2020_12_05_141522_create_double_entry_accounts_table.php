<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoubleEntryAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->integer('business_id')->unsigned()->index();
            $table->foreign('business_id')->references('id')->on('business');

            $table->bigInteger('account_type_id')->unsigned()->index();
            $table->foreign('account_type_id')->references('id')->on('acc_account_types');

            $table->bigInteger('category_id')->unsigned()->index();
            $table->foreign('category_id')->references('id')->on('acc_account_categories');

            $table->integer('created_by')->unsigned()->index();
            $table->foreign('created_by')->references('id')->on('users');


            $table->string('account_code',20);
            $table->string('account_name',120);
            $table->string('account_no',20);
           
            $table->double('balance',24,4);

            $table->tinyInteger('is_active');
            $table->tinyInteger('is_bank_ac');
            $table->boolean('is_default')->default(0);
            
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
        Schema::dropIfExists('acc_accounts');
    }
}
