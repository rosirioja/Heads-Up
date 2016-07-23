<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('alert_id')->unsigned()->default(0);
            $table->string('message')->default('');
            $table->string('data')->default('');
            $table->string('client_correlation')->default('');
            $table->string('response')->default('');
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->foreign('alert_id')->references('id')->on('alerts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions');
    }
}
