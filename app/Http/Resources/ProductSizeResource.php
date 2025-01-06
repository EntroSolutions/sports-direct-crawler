<?php

namespace App\Http\Resources;

use App\Models\ColorImage;
use App\Models\CsCartSetting;
use App\Models\ProductCategory;
use App\Models\Translate;
use App\MyMall\Traits\CurrencyTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductSizeResource extends JsonResource
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
        $mainImage = ColorImage::where('product_color_id', $this->product_color_id)
            ->first();

        if(app()->getLocale() == 'en'){
            $response = [
                'product_code' => $this->sd_size_id,
                'product' => $this->product->brand->name . ' ' .$this->product->name,
                'full_description' => $this->product->description,
                'price' => $this->getPriceForCsCart(), // @ todo pass the correct price
                'list_price' => $this->getPriceForCsCart(true),
                'main_pair' => [
                    'detailed' => [
                        'image_path' => asset($mainImage->image),
                        'http_image_path' => asset($mainImage->image),
                        'alt' => $this->product->brand->name . ' ' .$this->product->name,
                    ]
                ],
                'image_pairs' => $this->getAdditionalImages($mainImage->id),
                'category_ids' => $this->product->productCategories()->withMymallId()->get()->map(function (ProductCategory $productCategory) {
                    return array_merge(array_column($productCategory->category->subcategories ?? [], 'mymall_id'), [$productCategory->category->mymall_id]);
                })->first(),
                'amount' => $this->quantity,
                'lang_code' => Translate::LOCALES[app()->getLocale()],
                'tax_ids' => CsCartSetting::getTaxFeatureIds()->toArray(),
                'status' => ($this->price && $this->quantity  > 0)
                    ? 'A'
                    : 'D',
//                'status' => $this->when(!$this->mymall_id, 'D')
            ];

        } else{
            $response = [
                'product_code' => $this->sd_size_id,
                'product' => $this->product->brand->name . ' ' .$this->product->name,
                'full_description' => $this->product->description,
                'lang_code' => Translate::LOCALES[app()->getLocale()],
//                'status' => ($this->price && $this->quantity  > 0)
//                    ? 'A'
//                    : 'D',
                'status' => $this->when(!$this->mymall_id, 'D')

            ];
        }

        return $response;
    }

    private function getAdditionalImages($mainImageId)
    {
        $additionalImages = [];

        $colorImages = ColorImage::where('product_color_id', $this->product_color_id)
            ->where('id', '!=', $mainImageId)
            ->get();

        foreach ($colorImages as $key => $colorImage) {
            $additionalImages[] = [
                'detailed' => [
                    'image_path' => asset($colorImage->image),
                    'http_image_path' => asset($colorImage->image),
                    'alt' => $this->product->brand->name . ' ' .$this->product->name . '_' .$key,
                ]
            ];
        }

        return $additionalImages;
    }
}
