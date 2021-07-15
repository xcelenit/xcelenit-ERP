<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_branches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('vendor_id')->unsigned()->index()->nullable();
            $table->foreign('vendor_id')->references('id')->on('contacts');
            $table->string('branch_name',125);
            $table->boolean('is_active');
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
        Schema::dropIfExists('contact_branches');
    }
}
