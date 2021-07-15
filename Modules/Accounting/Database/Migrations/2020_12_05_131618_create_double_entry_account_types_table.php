<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Accounting\Entities\DoubleEntryAccountType;

class CreateDoubleEntryAccountTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_account_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type_code',20);
            $table->string('type',30);
            $table->string('trs_type',6);
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
        Schema::dropIfExists('acc_account_types');
    }
}
