<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSize;
use App\MyMall\Classes\ParseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LiveChecks extends Controller
{
    public function checkProduct(Request $request)
    {
        $productSize = ProductSize::byMymallId($request->mymall_id)->first();
        if(!$productSize){
            $product = Product::byMyMallId($request->mymall_id)->first();
            if($product && $product->sizes()->count() == 1){
                $productSize = $product->sizes()->first();
            }
        }


        if(!$productSize){
            return response()->json([
                'price' => '',
                'qty' => '',
                'available' => true // No id in the DB, Not setup by our vendor
            ]);
        }

        $quantity = 0;

        if($productSize->quantity > 0){

            $available = true;

            $theirProduct = ParseProduct::instance()->getById($productSize->sd_size_id);

            // At this point we have a mapped product so if the response is empty array, the product is not availavle
            if(empty($theirProduct)){
                $available = false;
            }

            $sdSize = null;

            if(isset($theirProduct['colours'])) {

                foreach ($theirProduct['colours'] as $color) {

                    $sdSize = Arr::where($color['sizes'], function ($size, $key) use ($productSize) {
                        return $size['sizeID'] == $productSize->sd_size_id;
                    });

                    if ($sdSize)
                        break;
                }
            }

            if(empty($sdSize)){

                $available = false;

            } else {

                $sdSize = Arr::first($sdSize);

                if($sdSize){
                    $quantity = $sdSize['onHandQty'];
                }

                if($sdSize && $sdSize['onHandQty'] <= 0){
                    $available = false;
                }

            }

        } else{
            $available = false;
        }

        return response()->json([
            'price' => '',
            'qty' => $quantity,
            'available' => $available
        ]);
    }
}
