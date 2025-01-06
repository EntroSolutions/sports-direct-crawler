<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned()->index();
            $table->bigInteger('size_id')->unsigned()->index();
            $table->bigInteger('color_id')->unsigned()->index();
            $table->bigInteger('product_color_id')->unsigned()->index();
            $table->bigInteger('sd_size_id')->unsigned()->index();
            $table->bigInteger('mymall_id')->unsigned()->nullable()->default(null)->index();
            $table->string('name');
            $table->integer('quantity');
            $table->double('price', 10, 2);
            $table->double('price_old', 10, 2)->default(0);
            $table->double('discount_amount', 10, 2)->default(0);
            $table->string('stock_level');
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
        Schema::dropIfExists('product_sizes');
    }
}
