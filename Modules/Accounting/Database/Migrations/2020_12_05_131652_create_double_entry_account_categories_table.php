<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoubleEntryAccountCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_account_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
             
            $table->integer('business_id')->unsigned()->index();
            $table->foreign('business_id')->references('id')->on('business');

            $table->bigInteger('account_type_id')->unsigned()->index();
            $table->foreign('account_type_id')->references('id')->on('acc_account_types');
                        
            
            $table->string('category_code',20);
            $table->string('category_name',120);                       
            $table->integer('sort_order');
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
        Schema::dropIfExists('acc_account_categories');
    }
}
