<?php

namespace App\MyMall\Traits;

use App\Models\SkipBrandRule;
use App\Models\SkipCategoryRule;
use App\Models\SkipProductRule;
use App\Models\SkipProductTitleRule;
use App\Models\SkipSdDiscountRule;
use App\Models\SkipSdPriceRule;
use App\Models\SkipSdSizeRule;

trait ProductSkipTrait
{
    public function scopeApplySkipFilters($query)
    {

        return $query->filterSkipProduct()
            ->filterSkipCategories()
            ->filterSkipBrands()
            ->filterSkipProductByWord()
            ->filterSkipProductByDiscount()
            ->filterSkipProductByPrice()
            ->filterSkipSize();

    }

    public function scopeFilterSkipCategories($query)
    {
        return $query->whereNotIn('product_categories.category_id', SkipCategoryRule::where('skip', 1)->pluck('category_id'));
    }

    public function scopeFilterSkipBrands($query)
    {
        return $query->whereNotIn('products.brand_id', SkipBrandRule::where('skip', 1)->pluck('brand_id'));
    }

    public function scopeFilterSkipProduct($query)
    {
        return $query->whereNotIn('products.sd_product_id', SkipProductRule::pluck('sd_product_id'));
    }

    public function scopeFilterSkipProductByWord($query)
    {

        $skipWords = SkipProductTitleRule::pluck('word');

        foreach ($skipWords as $skipWord){
            $query->where('products.name', 'NOT LIKE', '%' . $skipWord . '%');
        }

        return $query;
    }

    public function scopeFilterSkipProductByDiscount($query)
    {

        $skipDiscountRules = SkipSdDiscountRule::pluck('price_from', 'price_to');

        foreach ($skipDiscountRules as $priceTo => $priceFrom){

                $query->with(['sizes' => function($q) use($priceFrom, $priceTo){
                    $q->whereNotBetween('discount_amount', [$priceFrom, $priceTo]);
                }]);
        }

        return $query;
    }

    public function scopeFilterSkipProductByPrice($query)
    {

        $skipPriceRules = SkipSdPriceRule::pluck('price_from', 'price_to');

        foreach ($skipPriceRules as $priceTo => $priceFrom){

            $query->with(['sizes' => function($q) use($priceFrom, $priceTo){
                $q->whereNotBetween('price', [$priceFrom, $priceTo]);
            }]);
        }

        return $query;
    }

    public function scopeFilterSkipSize($query)
    {
        return $query->whereNotIn('ps.size_id', SkipSdSizeRule::where('skip', 1)->pluck('size_id'));
    }
}
