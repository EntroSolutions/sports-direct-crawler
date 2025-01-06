<?php

namespace App\Jobs;

use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductSizeResource;
use App\Http\Resources\ProductVariationsResource;
use App\Http\Resources\ProductWithOneSizeResource;
use App\Models\CsCartSetting;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Translate;
use App\MyMall\Traits\CsCartApi;
use App\MyMall\Traits\ProductSkipTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PushProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use CsCartApi;

    protected int $productId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Apply skip filters
        $product = Product::select('products.*')
            ->join('product_sizes as ps', 'ps.product_id', 'products.id')
            ->join('product_categories', 'product_categories.product_id', 'products.id')
            ->applySkipFilters()
            ->groupBy('products.id')
            ->where('products.id', $this->productId)
            ->first();

        if (!$product) {
            if (env('QUEUE_CONNECTION') != 'sync')
                return true;
        }

        $mappedCategoriesCount = $product->productCategories()
            ->join('categories as c', 'c.id', 'product_categories.category_id')
            ->whereNotNull('mymall_id')
            ->count();

        if (!$mappedCategoriesCount) {
//            dump('Skipping product no mapped categories ' . $product->name . ' | Locale ' . app()->getLocale());
            if (env('QUEUE_CONNECTION') != 'sync')
                return true;
        }

       $productResource = $this->getProductResource($product);

        // Create or update variations
//        $productVariations = new ProductVariationsResource($product);

//        if (empty($productVariations->toArray(request())['combinations'])) {
//            dump('Skipping product because no variation found or no variataions mapped ' . $product->name . ' | Locale ' . app()->getLocale());
//            if (env('QUEUE_CONNECTION') != 'sync')
//                return true;
//        }

        /**
         * Check if product is created and do not have variants with no mymall_id (new options added)
         */
        if ($product->mymall_id && !$product->sizes()->whereNull('mymall_id')->count()) {

            $this->updateProduct($productResource);

            foreach ($product->sizes as $productSize) {
                $this->updateProductVariationByLocale($productSize);
            }

        } else {

            /**
             * If the product is created byt the variations are not (missing color, size mymall_ids, etc.)
             */
            if ($product->mymall_id) {

                /*
                 * We have the product, but we need to check if the size is created, or it's a new size in the system
                 * because the product and the first size/variation are having the same mymall_id, but the product
                 * do not have price properties
                 */
                if($productSize = $product->sizes()->where('mymall_id', $product->mymall_id)->first()){
                    $response = $this->updateProduct(new ProductSizeResource($productSize));
                } else{
                    $response = $this->updateProduct($productResource);
                }


            } else {
                $response = $this->createProduct($productResource);
            }

            $response = json_decode($response, true);

            // Deleted product but myMall ID still attached to the product
            if (isset($response['status']) && $response['status'] == 404) {
                $product->mymall_id = null;
                $product->save();

                return $this->handle();

            } else {

                $product->mymall_id = $response['product_id'];
                $product->save();

                $product->refresh();

                // Create product
                foreach (Translate::LOCALES as $localLocale => $csCartLocale) {

                    // En already set up when product is created
                    if($localLocale == 'en')
                        continue;

                    app()->setLocale($localLocale);

                    $productResource = $this->getProductResource($product);

                    $this->updateProduct($productResource);
                }

                app()->setLocale('en');
            }

        }

        // Create or update variations
        $productVariations = new ProductVariationsResource($product);

        if (!empty($productVariations->toArray(request())['combinations']))
            $this->createVariations($productVariations, $productResource);

        app('queue.worker')->shouldQuit  = 1;

        return true;
    }

    private function createProduct(mixed $productResource)
    {
//        dump('Creating product: ' . $productResource->name . ' SD_ID ' . $productResource->sd_product_id);

        $url = config('cscart.api_base') . config('cscart.create_product_url');

        return $this->postRequest($url, $productResource->toJson());
    }

    private function updateProduct(mixed $productResource)
    {

        if ($productResource->product) {
            $productId = $productResource->product->id;
//            dump('Updating product: ' . $productResource->product->name . ' SD_SIZE_ID ' . $productResource->sd_size_id . ' | Locale ' . app()->getLocale());
        } else {
            $productId = $productResource->id;
//            dump('Updating product: ' . $productResource->name . ' SD_ID ' . $productResource->sd_product_id . ' | Locale ' . app()->getLocale());
        }

        $url = config('cscart.api_base') . Str::replace(':product_id:', $productResource->mymall_id, config('cscart.update_product_url'));

        return $this->putRequest($url, $productResource->toJson());
    }

    private function createVariations(ProductVariationsResource $productVariations, mixed $productResource)
    {
//        dump('Creating product variations: ' . $productResource->name . ' SD_ID ' . $productResource->sd_product_id);

        $url = config('cscart.api_base') . Str::replace(':product_id:', $productResource->mymall_id, config('cscart.generate_product_variations'));

        $response = $this->postRequest($url, $productVariations->toJson());

        $response = json_decode($response);

        if (isset($response->group->products)) {

            foreach ($response->group->products as $productResponse) {

                $this->setMymallProductIdToSize($productResponse, $productResource);

            }
        }

        foreach ($productResource->sizes()->whereNotNull('mymall_id')->get() as $productSize){
            $this->updateProductVariationByLocale($productSize);
        }
    }

    private function setMymallProductIdToSize(mixed $productResponse, ProductResource $productResource)
    {

        $firstSize = $productResource->sizes()
            ->select('product_sizes.*')
            ->join('sizes as s', 's.id', 'product_sizes.size_id')
            ->join('colors as c', 'c.id', 'product_sizes.color_id')
            ->whereNotNull('c.mymall_id')
            ->whereNotNull('s.cs_cart_setting_id')
            ->first();

        $sizeFeatureResponse = Arr::first(
            Arr::where($productResponse->feature_values, function ($value, $key) use ($productResource, $firstSize) {
                return $value->feature_id == CsCartSetting::where('id', $firstSize->size->cs_cart_setting_id)->first()->feature_id;
            })
        );

        $colorFeatureResponse = Arr::first(
            Arr::where($productResponse->feature_values, function ($value, $key) {
                return $value->feature_id == CsCartSetting::getCsColorFeatureId();
            })
        );

        $productSize = ProductSize::select('product_sizes.*', 's.id as size_id', 's.mymall_id')
            ->join('colors as c', 'c.id', 'product_sizes.color_id')
            ->join('sizes as s', 's.id', 'product_sizes.size_id')
            ->where('product_id', $productResource->id)
            ->where('s.mymall_id', $sizeFeatureResponse->variant_id)
            ->where('c.mymall_id', $colorFeatureResponse->variant_id)
            ->orderBy('price', 'asc')
            ->first();

        if (!$productSize)
            return true;

        $productSize->mymall_id = $productResponse->product_id;
        $productSize->save();

    }

    private function updateProductVariationByLocale(ProductSize $productSize)
    {

        foreach (Translate::LOCALES as $localLocale => $csCartLocale) {

            app()->setLocale($localLocale);

            $response = $this->updateProduct(new ProductSizeResource($productSize));
            $response = json_decode($response, true);
//info($response);

            // Deleted product but myMall ID still attached to the product
            if (isset($response['status']) && $response['status'] == 404) {
                $productSize->mymall_id = null;
                $productSize->save();

                return $this->updateProductVariationByLocale($productSize);
            } else {

                $productSize->mymall_id = $response['product_id'];
                $productSize->save();
            }


        }

        app()->setLocale('en');
    }

    private function getProductResource(Product $product): ProductWithOneSizeResource|ProductResource
    {
        $countMappedProductSizes = $product->sizes()
            ->select('product_sizes.*')
            ->join('sizes as s', 's.id', 'product_sizes.size_id')
            ->join('colors as c', 'c.id', 'product_sizes.color_id')
            ->whereNotNull('s.cs_cart_setting_id')
            ->whereNotNull('c.mymall_id')
            ->count();

        if($countMappedProductSizes == 1){
            $productResource = new ProductWithOneSizeResource($product);
        } else {
            $productResource = new ProductResource($product);
        }

        return $productResource;
    }
}
