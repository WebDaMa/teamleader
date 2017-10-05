<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->string('product_id');
            $table->decimal('unit_price');
            $table->integer('quantity');
            $table->decimal('subtotal')->default(0);
            $table->decimal('discount')->default(0);
            $table->text('discount_and_reason');
            $table->decimal('total')->default(0);
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
