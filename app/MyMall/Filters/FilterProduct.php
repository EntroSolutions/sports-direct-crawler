<?php

namespace App\MyMall\Filters;

use App\Models\SkipBrandRule;
use App\Models\SkipProductRule;
use App\Models\SkipProductTitleRule;
use App\Models\SkipSdDiscountRule;
use App\Models\SkipSdPriceRule;
use App\MyMall\Traits\Singleton;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FilterProduct implements FilterInterface
{
    use Singleton;
    private Collection $skipDiscountPriceRules;
    private Collection $skipPriceRules;
    private Collection $skipProductTitleRules;
    private Collection $skipProductSkuRules;
    public array $product;

    public function __construct()
    {
        $this->skipDiscountPriceRules = SkipSdDiscountRule::all();
        $this->skipPriceRules = SkipSdPriceRule::all();
        $this->skipProductTitleRules = SkipProductTitleRule::all();
        $this->skipProductSkuRules = SkipProductRule::all();
    }

    public function shouldSkip(mixed $product) : bool
    {
//        - на база бранд
//        - на база отстъпка при източника
//        - на база цена при източника
//        - на база категория при източника
//        - на база дума в заглавието
//        - на база ръчно посочване на SKU
        // TODO: Implement check() method.

        $this->product = $product;

        // Sometimes the SD api returns 404
        if(empty($product)){
            dump('Product not found. Empty array returned');
            return true;
        }

        return $this->check();

    }

    private function check() : bool
    {

        // Skip product (SKU)
        if($this->shouldSkipProductSku()) {
            dump('Skipping product SKU');
            return true;
        }

        // Skip brand
        if($this->shouldSkipBrand()) {
            dump('Skipping brand');
            return true;
        }

        // Skip discount
        if($this->shouldSkipDiscount()) {
            dump('Skipping discount');
            return true;
        }

        // Skip price
        if($this->shouldSkipPrice()) {
            dump('Skipping price');
            return true;
        }

        // Skip title word
        if($this->shouldSkipTitleWord()) {
            dump('Skipping title');
            return true;
        }

        return false;
    }

    private function shouldSkipBrand() : bool
    {
        $skipBrandRule = SkipBrandRule::find($this->product['brand_id']);

        return $skipBrandRule
            ? $skipBrandRule->skip
            : false;
    }

    private function shouldSkipDiscount() : bool
    {
        $colors = Arr::where($this->product['colors'],function ($color, $colorKey){

            $sizes = Arr::where($color['sizes'],function ($size, $sizeKey){

                $skipDiscount = $this->skipDiscountPriceRules
                    ->search(function ($skipDiscountRule) use($size){
                        return $size['discount_amount'] >= $skipDiscountRule->price_from &&  $size['discount_amount'] <= $skipDiscountRule->price_to;
                    });

                if ( $skipDiscount === false ) {
                    return $size;
                }

            });

            if(!empty($sizes))
                return $color;

        });

        // Set the correct array to product object for later use
        $this->product['colors'] = $colors;

        if( ! count($this->product['colors']) ) {
            return true;
        }

        return false;
    }

    private function shouldSkipPrice() : bool
    {

        $colors = Arr::where($this->product['colors'],function ($color, $colorKey){

            $sizes = Arr::where($color['sizes'],function ($size, $sizeKey){

                $skipDiscount = $this->skipPriceRules
                    ->search(function ($skipPriceRule) use($size){
                        return $size['price'] >= $skipPriceRule->price_from &&  $size['price'] <= $skipPriceRule->price_to;
                    });

                if ( $skipDiscount === false ) {
                    return $size;
                }

            });

            if(!empty($sizes))
                return $color;

        });

        // Set the correct array to product object for later use
        $this->product['colors'] = $colors;

        if( ! count($this->product['colors']) ) {
            return true;
        }

        return false;
    }

    private function shouldSkipTitleWord() : bool
    {
        $filtered = $this->skipProductTitleRules->search(function($skipTitleRule, $key){
            return Str::contains(Str::lower($this->product['name']), Str::lower($skipTitleRule->word));
        });

        if ( $filtered !== false ) {
            return true;
        }

        return false;
    }

    private function shouldSkipProductSku()
    {
        $filtered = $this->skipProductSkuRules->search(function($skipProductRule, $key){
            return $this->product['id'] == $skipProductRule->sd_product_id;
        });

        if ( $filtered !== false ) {
            return true;
        }

        return false;
    }
}
