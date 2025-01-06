<?php

namespace App\MyMall\Classes;


use App\Models\ProductColor;
use App\Models\ProductSize;
use App\MyMall\Traits\Singleton;
use \App\Models\Product;

class ImportProduct extends PriceDecisions
{
    use Singleton;

    private $categoryId;

    public function import(array $sdProduct, $categoryId): bool|Product
    {

        if (!$this->shouldImport($sdProduct))
            return false;

        $product = $this->createProduct($sdProduct);

        $productCategory = $this->createProductCategory($product, $categoryId);

        foreach ($sdProduct['colors'] as $sdColor) {

            $productColor = $this->createProductColor($product, $sdColor);

            foreach ($sdColor['sizes'] as $size) {
                $productSize = $this->createProductSize($product, $productColor, $size);
            }

            $this->createColorImages($product, $productColor, $sdColor);

        }

        return $product;
    }

    private function createProduct(array $sdProduct): Product
    {
        // Override product image. Do not update it if set to true
        $product = Product::where('sd_product_id', $sdProduct['id'])->first();

        if ($product && $product->override_product_image) {
            unset($sdProduct['image']);
        }

        if ($product && $product->override_name) {
            unset($sdProduct['name']);
        }



        return Product::updateOrCreate(
            ['sd_product_id' => $sdProduct['id']],
            $sdProduct
        );
    }

    private function createProductCategory(Product $product, $categoryId)
    {
        $product->productCategories()->updateOrCreate(
            [
                'product_id' => $product->id,
                'category_id' => $categoryId,
            ]
        );
    }

    private function createProductColor(Product $product, mixed $sdColor)
    {
        $productColor = $product->colors()->updateOrCreate(
            [
                'product_id' => $product->id,
                'color_id' => $sdColor['color_id'],
                'sd_color_id' => $sdColor['sd_color_id'],
            ],
            [
                'name' => $sdColor['name'],
            ]
        );

        return $productColor;
    }

    private function createProductSize(Product $product, ProductColor $productColor, mixed $size)
    {
        $productSize = $product->sizes()->where([
                'product_id' => $product->id,
                'product_color_id' => $productColor->id,
                'size_id' => $size['size_id'],
                'color_id' => $productColor->color_id,
                'sd_size_id' => $size['sd_size_id'],
            ])
            ->first();

        $updateOrCreateData = [
            'name' => $size['name'],
            'quantity' => $size['quantity'],
            'stock_level' => $size['stock_level'],
        ];

        if(!$productSize || !$productSize->override_price ){
            $updateOrCreateData['price'] = $size['price'];
            $updateOrCreateData['price_old'] = $size['price_old'];
            $updateOrCreateData['discount_amount'] = $size['discount_amount'];
        }

        return $product->sizes()->updateOrCreate(
            [
                'product_id' => $product->id,
                'product_color_id' => $productColor->id,
                'size_id' => $size['size_id'],
                'color_id' => $productColor->color_id,
                'sd_size_id' => $size['sd_size_id'],
            ],
            $updateOrCreateData
        );
    }

    private function createColorImages(Product $product,  ProductColor $productColor, mixed $sdColor)
    {

        foreach ($sdColor['alternateImages'] as $alternateImage) {

            // If the image exists and it's not mark override
            if ($colorImage = $productColor->colorImages()->where('original_image', $alternateImage)->first()) {
                if (!$colorImage->override) {
                    $colorImage->image = $alternateImage;
                    $colorImage->save();
                }
            } else {
                // Create it
                $productColor->colorImages()->create([
                        'product_color_id' => $productColor->id,
                        'image' => $alternateImage,
                        'original_image' => $alternateImage
                    ]
                );
            }
        }
    }
}
