<?php

namespace App\Http\Resources;

use App\Models\ColorImage;
use App\Models\CsCartSetting;
use App\Models\ProductCategory;
use App\Models\Translate;
use App\MyMall\Traits\CurrencyTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use function Composer\Autoload\includeFile;

class ProductWithOneSizeResource extends JsonResource
{

    use CurrencyTrait;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $firstSize = $this->sizes()
            ->select('product_sizes.*')
            ->join('sizes as s', 's.id', 'product_sizes.size_id')
            ->join('colors as c', 'c.id', 'product_sizes.color_id')
            ->whereNotNull('s.cs_cart_setting_id')
            ->whereNotNull('c.mymall_id')
            ->first();

        $mainImage = $this->colors()->where('color_id', $firstSize->color_id)->first()->colorImages()->first();

        if(app()->getLocale() == 'en') {
            $response = [
//                'product_id' => $this->when($this->mymall_id, $this->mymall_id),
                'product_code' => $firstSize->sd_size_id,
                'product' => $this->brand->name . ' ' . $this->name,
                'lang_code' => Translate::LOCALES[app()->getLocale()],
                'company_id' => CsCartSetting::byType('vendor')->first()->feature_id,
                'full_description' => $this->description,
                'price' => $firstSize->getPriceForCsCart(),
                'list_price' => $firstSize->getPriceForCsCart(true),
                'amount' => $firstSize->quantity,
                'main_pair' => [
                    'detailed' => [
                        'image_path' => asset($mainImage->image),
                        'http_image_path' => asset($mainImage->image),
                        'alt' => $this->brand->name . ' ' . $this->name,
                    ]
                ],
                'image_pairs' => $this->getAdditionalImages($mainImage),
                'category_ids' => $this->productCategories()->withMymallId()->get()->map(function (ProductCategory $productCategory) {
                    return array_merge(array_column($productCategory->category->subcategories ?? [], 'mymall_id'), [$productCategory->category->mymall_id]);
                })->first(),
                'product_features' => $this->getProductFeatures($request),
                'tax_ids' => CsCartSetting::getTaxFeatureIds()->toArray(),
                'status' => ($firstSize->price && $firstSize->quantity > 0)
                    ? 'A'
                    : 'D'
//                'status' => $this->when(!$this->mymall_id, 'D') // Use the product mymall id, because it has only one size
            ];
        }
        else{
            $response = [
                'product' => $this->brand->name . ' ' . $this->name,
                'lang_code' => Translate::LOCALES[app()->getLocale()],
                'full_description' => $this->description,
                'product_features' => $this->getProductFeatures($request),
            ];
        }

        return $response;
    }

    private function getProductFeatures($request)
    {

        $firstSize = $this->sizes()
            ->select('product_sizes.*')
            ->join('sizes as s', 's.id', 'product_sizes.size_id')
            ->join('colors as c', 'c.id', 'product_sizes.color_id')
            ->whereNotNull('s.cs_cart_setting_id')
            ->whereNotNull('c.mymall_id')
            ->first();

        if(!$firstSize || !$firstSize->size || !$firstSize->size->cs_cart_setting_id)
            return [];

        $csSizeFeatureId = CsCartSetting::byType('size')
            ->where('id', $this->sizes()->first()->size->cs_cart_setting_id)
            ->first()
            ->feature_id;

        $csColorFeatureId = CsCartSetting::byType('color')
            ->first()
            ->feature_id;

        $csBrandFeatureId = CsCartSetting::byType('brand')
            ->first()
            ->feature_id;

        $csDeliveryFeature = CsCartSetting::byType('delivery')
            ->first();

        $productVariations = new ProductVariationsResource($this);
        $firstVariation = $productVariations->toArray($request);

        if(empty($firstVariation['combinations']))
            return [];

        $features[strval($csColorFeatureId)] = [
            "feature_type" => "S",
            'variant_id' => strval(array_key_last(array_flip(reset($firstVariation['combinations'])))),
            "purpose" => "group_variation_catalog_item"
        ];

        $features[strval($csSizeFeatureId)] = [
            "feature_type" => "S",
            'variant_id' => strval(array_key_first(array_flip(reset($firstVariation['combinations'])))),
            "purpose" => "group_variation_catalog_item"
        ];

        if($this->brand){
            $features[strval($csBrandFeatureId)] = [
                "feature_type" => "S",
                'variant_id' => $this->brand->mymall_id,
                "purpose" => "group_variation_catalog_item"
            ];
        }

        if($csDeliveryFeature){
            $features[strval($csDeliveryFeature->feature_id)] = [
                "feature_type" => "E",
                'variant_id' => $csDeliveryFeature->feature_variant_id,
                "purpose" => "organize_catalog"
            ];
        }

        return (object)$features;
    }

    private function getAdditionalImages(ColorImage $mainImage)
    {
        $additionalImages = [];

        $colorImages = ColorImage::where('product_color_id', $mainImage->product_color_id)
            ->where('id', '!=', $mainImage->id)
            ->get();

        foreach ($colorImages as $key => $colorImage) {
            $additionalImages[] = [
                'detailed' => [
                    'http_image_path' => asset($colorImage->image),
                    'image_path' => asset($colorImage->image),
                    'alt' => $this->brand->name . ' ' . $this->name,
                ]
            ];
        }

        return $additionalImages;
    }
}
