<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('identifier',30)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');
            $table->string('user_phone', 20);
            $table->string('count', 10);
            $table->string('amount', 10);
            $table->string('stk_response_code', 20)->nullable();
            $table->string('stk_merchant_request_id', 50)->nullable();
            $table->string('stk_checkout_request_id', 50)->nullable();
            $table->string('stk_error_code', 50)->nullable();
            $table->string('stk_error_message')->nullable();
            $table->string('stk_server_error')->nullable();
            $table->enum('stk_status', array(0,1,2));// 0 -> received, 1-> money_received, 2 -> money_not_received
            $table->string('callback_response_code', 20)->nullable();
            $table->string('callback_merchant_request_id', 50)->nullable();
            $table->string('callback_checkout_request_id', 50)->nullable();
            $table->string('callback_result_desc', 100)->nullable();
            $table->string('callback_phone')->nullable();
            $table->string('callback_amount')->nullable();
            $table->string('mpesa_receipt_number')->nullable();
            $table->string('mpesa_transaction_date', 50)->nullable();
            $table->string('callback_db_error', 200)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('order_id')->references('id')->on('user_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
