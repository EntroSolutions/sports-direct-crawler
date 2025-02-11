<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOverrideAndImageFromProductColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_colors', function (Blueprint $table) {
            if(Schema::hasColumn('product_colors', 'image')){
                $table->dropColumn('image');
            }

            if(Schema::hasColumn('product_colors', 'override_color_image')){
                $table->dropColumn('override_color_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_colors', function (Blueprint $table) {
            //
        });
    }
}
