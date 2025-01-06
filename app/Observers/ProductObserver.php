<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product)
    {
        if( request()->is('admin/*') && $product->getOriginal('image') != $product->image){

            /** Set Overrider products image attribute !!! */
            $product->override_product_image = 1;

        }
    }
}
