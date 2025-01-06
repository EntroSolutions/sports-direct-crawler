<?php

namespace App\Http\Resources;

use App\Models\CsCartSetting;
use App\Models\Size;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ProductVariationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'combinations' => $this->getVariationCombinations()
        ];
    }

    private function getVariationCombinations(): array
    {
        $combinations = [];


        $colors = $this->colors()
            ->select('product_colors.color_id')
            ->join('colors', 'colors.id', 'product_colors.color_id')
            ->join('product_sizes', 'product_colors.id', 'product_sizes.product_color_id') // Join to make the ordering
            ->whereNotNull('colors.mymall_id')
            ->with(['color'])
            ->orderBy('product_sizes.price', 'asc')
            ->groupBy('product_colors.color_id')
            ->get();

        foreach ($colors as $productColor) {


            $sizesData = $this->sizes()
                ->select('product_sizes.size_id', 'product_sizes.id', 'product_sizes.price', 'product_sizes.color_id')
                ->where('product_sizes.color_id', $productColor->color_id)
                ->join('sizes as s', 's.id', 'product_sizes.size_id')
                ->whereNotNull('s.cs_cart_setting_id')
                ->whereIn('size_id', Size::whereIn('id', $this->sizes()->pluck('size_id'))->whereNotNull('mymall_id')->pluck('id'))
                ->with(['size'])
                ->get();

            // Group by not ordering the items as expected (by price asc so the lowest price item is created as main product). Filtering the collection manually...

            $sizes = collect([]);

            $sizesData = $sizesData->groupBy('size_id');
            foreach ($sizesData as $sizeGroup){
                $sizes->push($sizeGroup->sortBy('price')->first());
            }

            foreach ($sizes as $productSize) {

                $combinations[] = [
                    CsCartSetting::where('id', $productSize->size->cs_cart_setting_id)->first()->feature_id => $productSize->size->mymall_id,
                    CsCartSetting::byType('color')->first()->feature_id => $productColor->color->mymall_id,
//                            'sizeName' => $productSize->size->name,
//                            'colorName' => $productColor->color->name,
                ];

            }

        }

        // remove duplicated
        $combinations = array_map("unserialize", array_unique(array_map("serialize", $combinations)));

        return $combinations;
    }
}
