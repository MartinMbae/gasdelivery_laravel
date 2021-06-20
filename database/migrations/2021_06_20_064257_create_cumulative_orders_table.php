<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCumulativeOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cumulative_orders', function (Blueprint $table) {
            $table->id();
            $table->string('user_orders_gases',100);
            $table->string('user_orders_accessory',100);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('address_id');
            $table->string('order_instructions')->nullable();
            $table->enum('status', array(0,1,2,3,4));//0->new order, 1->completed, 2->Cancelled, 3->Rejected Order// 4->paid
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('address_id')->references('id')->on('user_addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cumulative_orders');
    }
}
