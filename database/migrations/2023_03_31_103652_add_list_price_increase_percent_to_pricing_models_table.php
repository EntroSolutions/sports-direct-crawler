<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddListPriceIncreasePercentToPricingModelsTable extends Migration
{

    private $listPriceIncreasePercents = [
        0 => 120,
        1 => 152,
        2 => 180,
        3 => 190,
        4 => 205,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pricing_models', function (Blueprint $table) {
            $table->double('list_price_increase_percent', 10, 2)->after('price_increase_percent');
        });

        foreach (range(5, 1000, 5) as $step) {

            $models = \App\Models\PricingModel::where('price_to', $step)->get();

            foreach ($models as $key => $model) {
                $model->list_price_increase_percent = $this->listPriceIncreasePercents[$key];
                $model->save();
            }

        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pricing_models', function (Blueprint $table) {
            if(Schema::hasColumn('pricing_models', 'list_price_increase_percent')){
                $table->dropColumn('list_price_increase_percent');
            }
        });
    }
}
