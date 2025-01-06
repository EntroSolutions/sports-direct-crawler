<?php

namespace App\MyMall\Classes;

use App\Models\Category;
use App\Models\SkipCategoryRule;
use App\Models\SkipProductRule;
use App\MyMall\Classes\PriceDecisions;
use App\MyMall\Traits\Singleton;
use \App\Models\Product;

class ImportCategory extends PriceDecisions
{
    use Singleton;

    public function import( \SimpleXMLElement $categoryXml) : Category
    {

        $category = Category::firstOrCreate(
            ['url' => $categoryXml->loc],
        );

        if( ! $category->skipCategoryRule ){
            SkipCategoryRule::create([
                'category_id' => $category->id,
                'skip' => 0
            ]);
        }

        return $category;
    }
}
