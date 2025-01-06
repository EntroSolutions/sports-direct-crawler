<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMymallFeatureIdToSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sizes', function (Blueprint $table) {
            $table->integer('cs_cart_setting_id')->nullable()->default(null)->index()->after('mymall_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sizes', function (Blueprint $table) {
            if(Schema::hasColumn('sizes', 'cs_cart_setting_id')){
                $table->dropColumn('cs_cart_setting_id');
            }
        });
    }
}
