<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeatureVariantIdToCsCartSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cs_cart_settings', function (Blueprint $table) {
            $table->integer('feature_variant_id')->nullable()->default(null)->after('feature_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cs_cart_settings', function (Blueprint $table) {
            if(Schema::hasColumn('cs_cart_settings', 'feature_variant_id')){
                $table->dropColumn('feature_variant_id');
            }
        });
    }
}
