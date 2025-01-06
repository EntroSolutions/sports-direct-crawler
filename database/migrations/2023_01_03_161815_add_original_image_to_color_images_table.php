<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalImageToColorImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('color_images', function (Blueprint $table) {
            $table->string('original_image')->after('image')->nullable()->default('null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('color_images', function (Blueprint $table) {
            if(Schema::hasColumn('color_images', 'original_image')){
                $table->dropColumn('original_image');
            }
        });
    }
}
