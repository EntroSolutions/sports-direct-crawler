<?php

namespace App\Http\Resources;

use App\Models\CsCartSetting;
use App\Models\ProductCategory;
use App\Models\Translate;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if(app()->getLocale() == 'en'){
            $response = [
                'product_id' => $this->when($this->mymall_id, $this->mymall_id),
                'product' => $this->brand->name . ' ' . $this->name,
                'lang_code' => Translate::LOCALES[app()->getLocale()],
                'full_description' => $this->description,
                'price' => '0', // adding zero here when the product is created. Later when the variations are created the price will get updated per each variation
                'list_price' => '0', // adding zero here when the product is created. Later when the variations are created the price will get updated per each variation
                'amount' => '0', // adding zero here when the product is created. Later when the variations are created the price will get updated per each variation
                'company_id' => CsCartSetting::byType('vendor')->first()->feature_id,
                'main_pair' => [
                    'detailed' => [
                        'http_image_path' => asset($this->image),
                        'image_path' => asset($this->image),
                        'alt' => $this->brand->name . ' ' . $this->name
                    ]
                ],
                'category_ids' => $this->productCategories()->withMymallId()->get()->map(function (ProductCategory $productCategory) {
                    return array_merge(array_column($productCategory->category->subcategories ?? [], 'mymall_id'), [$productCategory->category->mymall_id]);
                })->first(),
                'product_features' => $this->getProductFeatures($request),
                'tax_ids' => CsCartSetting::getTaxFeatureIds()->toArray(),
                'status' => $this->when(!$this->mymall_id, 'D')
            ];
        } else {
            $response = [
                'product_id' => $this->when($this->mymall_id, $this->mymall_id),
                'product' => $this->brand->name . ' ' . $this->name,
                'lang_code' => Translate::LOCALES[app()->getLocale()],
                'full_description' => $this->description,
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
            ->whereNotNull('c.mymall_id')
            ->whereNotNull('s.cs_cart_setting_id')
            ->first();

        if(!$firstSize || !$firstSize->size || !$firstSize->size->cs_cart_setting_id)
            return [];

        $csSizeFeatureId = CsCartSetting::byType('size')
            ->where('id', $firstSize->size->cs_cart_setting_id)
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
}
