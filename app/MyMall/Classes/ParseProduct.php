<?php

namespace App\MyMall\Classes;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Size;
use App\Models\SkipBrandRule;
use App\Models\SkipCategoryRule;
use App\Models\Translate;
use App\MyMall\Classes\Parse;
use App\MyMall\Traits\CurlTrait;
use App\MyMall\Traits\Singleton;
use App\MyMall\Traits\TranslateTrait;
use Illuminate\Support\Arr;

class ParseProduct extends Parse
{

    use Singleton;

    use CurlTrait;
    use TranslateTrait;

    public function getById(int $mymall_product_id): array
    {
        $url = 'https://sportsdirect.com/API/product/v1/get?productId=' . $mymall_product_id;

        $productJson = $this->request($url);

        if (!$productJson) {
            return [];
        }

        return json_decode($productJson, true);
    }

    public function createOrUpdateProduct(int $product_id, mixed $category_id = null): array
    {
//        dump('Crawling SD product ' . $product_id);
//        dump(memory_get_usage() / (1024 * 1024) . 'MB');
//        dump(memory_get_usage() / (1024 * 1024) . 'MB');

        $productData = $this->getById($product_id);

        if (!$productData) {
            return [];
        }

        /** @var Brand $brand */
        $brand = Brand::firstOrCreateByName($productData['brand']);

        if (!$brand->skipBrandRule) {
            SkipBrandRule::create([
                'brand_id' => $brand->id,
                'skip' => 0
            ]);
        }

        $description = [];

        // Translate attributes
        if (isset($productData['displayAttributes']) && !empty($productData['displayAttributes'])) {

            foreach (Translate::LOCALES as $localLocale => $csCartLocale) {

                $description[$localLocale] = '';

                foreach ($productData['displayAttributes'] as $displayAttribute) {
                    $description[$localLocale] .= '<p><b>' . $this->translateString($displayAttribute['displayName'], $csCartLocale) . '</b>: ' . $this->translateString($displayAttribute['displayValue'], $csCartLocale) . '</p>';
                }

                $description[$localLocale] .= '';
            }
        }

        // Translate descriptions //// Mymall has only features
//        foreach (Translate::LOCALES as $localLocale => $csCartLocale) {
//
//            if( isset($description[$localLocale]) )
//                $description[$localLocale] .= $this->translateString($productData['description'], $localLocale);
//            else
//                $description[$localLocale] = $this->translateString($productData['description'], $localLocale);
//
//        }

        $product = [
            'id' => $productData['id'],
            'name' => [
                // Translations
                'en' => $productData['name'],
                'bg_BG' => $this->translateString($productData['name'], 'bg'),
                'el' => $this->translateString($productData['name'], 'el'),
                'ro' => $this->translateString($productData['name'], 'ro'),
            ],
            'description' => $description,
            'image' => $productData['defaultImages']['url'] ?? null,
            'brand_id' => $brand->id,
        ];

        $colors = [];
        foreach ($productData['colours'] as $colourId => $sdColor) {

            $color = Color::firstOrCrateByName($sdColor['colourName'], $colourId);

            $colors[$colourId] = [
                'name' => [
                    // Translations
                    'en' => $color->name,
                    'bg_BG' => $this->translateString($color->name, 'bg'),
                    'el' => $this->translateString($color->name, 'el'),
                    'ro' => $this->translateString($color->name, 'ro'),
                ],
                'color_id' => $color->id,
                'sd_color_id' => $sdColor['colourID'],
                'alternateImages' => Arr::pluck($sdColor['alternateImages']['zoomImages'], 'urlLarge')
            ];

            foreach ($sdColor['sizes'] as $sizeId => $sdSize) {

                $size = Size::firstOrCreateByName($sdSize['sizeName'], $category_id);

                $colors[$colourId]['sizes'][$sizeId] = [
                    'size_id' => $size->id,
                    'name' => [
                        // Translations
                        'en' => $size->name,
                        'bg_BG' => $this->translateString($size->name, 'bg'),
                        'el' => $this->translateString($size->name, 'el'),
                        'ro' => $this->translateString($size->name, 'ro'),
                    ],
                    'quantity' => $sdSize['onHandQty'],
                    'price' => $sdSize['baseCurrencyListPriceRaw'],
                    'price_old' => $sdSize['baseCurrencyTicketPriceRaw'],
                    'discount_amount' => $sdSize['youSaveAmount'],
                    'stock_level' => $sdSize['stockLevel'],
                    'sd_size_id' => $sdSize['sizeID'],
                ];
            }
        }

        $product['colors'] = $colors;

        return $product;
    }
}
