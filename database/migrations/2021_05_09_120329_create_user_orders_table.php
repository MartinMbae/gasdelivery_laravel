<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('gas_id');
            $table->string('count',2);
            $table->string('total_price',20);
            $table->string('order_instructions', 200)->nullable();
            $table->enum('status', array(0,1,2,3,4));//0->new order, 1->completed, 2->Cancelled, 3->Rejected Order// 4->paid
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('address_id')->references('id')->on('user_addresses');
            $table->foreign('gas_id')->references('id')->on('gases');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_orders');
    }
}
