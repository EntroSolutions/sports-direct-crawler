<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricingModelsTable extends Migration
{

    protected static $values = [
        [
            'price_increase_percent' => 100,
            'sd_discount'            => 19,
            'round'                  => 95,
        ],
        [
            'price_increase_percent' => 107,
            'sd_discount'            => 49,
            'round'                  => 95,
        ],
        [
            'price_increase_percent' => 115,
            'sd_discount'            => 69,
            'round'                  => 95,
        ],
        [
            'price_increase_percent' => 115,
            'sd_discount'            => 79,
            'round'                  => 95,
        ],
        [
            'price_increase_percent' => 115,
            'sd_discount'            => 100,
            'round'                  => 95,
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('pricing_models')) {
            Schema::create('pricing_models', function (Blueprint $table) {
                $table->id();
                $table->double('price_to', 10, 2);
                $table->double('price_increase_percent', 10, 2);
                $table->double('sd_discount', 10, 2);
                $table->integer('round');
                $table->timestamps();
            });
        }

        foreach (range(5, 1000, 5) as $step) {

            foreach (self::$values as $values){
                \App\Models\PricingModel::create([
                    'price_to'               => $step,
                    'price_increase_percent' => $values['price_increase_percent'],
                    'sd_discount'            => $values['sd_discount'],
                    'round'                  => $values['round'],
                ]);
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
        Schema::dropIfExists('pricing_models');
    }
}
