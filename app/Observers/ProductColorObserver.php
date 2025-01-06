<?php

namespace App\Observers;

use App\Models\ProductColor;

class ProductColorObserver
{
    public function saving(ProductColor $productColor)
    {

        if(request()->is('admin/*') && $productColor->getOriginal('image') != $productColor->image){

            /** Set Overrider products image attribute !!! */
            $productColor->override_color_image = 1;

        }

    }
}
