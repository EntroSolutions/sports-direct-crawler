<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNameToTextOnProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('product_colors', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('product_sizes', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('colors', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('sizes', function (Blueprint $table) {
            $table->text('name')->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->text('name')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
