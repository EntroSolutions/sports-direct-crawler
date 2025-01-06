<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes

    Route::crud('category', 'CategoryCrudController');
    Route::get('category/crawl-now/{categoryId}', [\App\Http\Controllers\Admin\CategoryCrudController::class, 'crawlNow'])->name('category.crawlNow');
    Route::get('category/get-cscart-categories', [\App\Http\Controllers\Admin\CategoryCrudController::class, 'getCsCartCategories'])->name('category.getCsCartCategories');


    Route::crud('product', 'ProductCrudController');
    Route::crud('color', 'ColorCrudController');
    Route::crud('size', 'SizeCrudController');
    Route::get('size/get-feature-variants', [\App\Http\Controllers\Admin\SizeCrudController::class, 'getCsCartSizes'])->name('size.getCsCartSizes');
    Route::crud('pricingmodel', 'PricingModelCrudController');
    Route::crud('productcolor', 'ProductColorCrudController');
    Route::crud('productsize', 'ProductSizeCrudController');
    Route::crud('skipbrandrule', 'SkipBrandRuleCrudController');
    Route::crud('skipproducttitlerule', 'SkipProductTitleRuleCrudController');
    Route::crud('skipsddiscountrule', 'SkipSdDiscountRuleCrudController');
    Route::crud('skipsdpricerule', 'SkipSdPriceRuleCrudController');
    Route::crud('skipproductrule', 'SkipProductRuleCrudController');
    Route::crud('skipcategoryrule', 'SkipCategoryRuleCrudController');
    Route::crud('translate', 'TranslateCrudController');
    Route::crud('colorimage', 'ColorImageCrudController');
    Route::crud('cscartsetting', 'CsCartSettingCrudController');
    Route::get('cscartsetting/get-cs-settings', [\App\Http\Controllers\Admin\CsCartSettingCrudController::class, 'getCsCartFeatures'])->name('cscartsetting.getCsCartFeatures');
    Route::get('cscartsetting/get-cs-feature-by-id', [\App\Http\Controllers\Admin\CsCartSettingCrudController::class, 'getCsCartFeatureById'])->name('cscartsetting.getCsCartFeatureById');
    Route::crud('brand', 'BrandCrudController');
    Route::crud('currency', 'CurrencyCrudController');
    Route::crud('skipsdsizerule', 'SkipSdSizeRuleCrudController');
}); // this should be the absolute last line of this file
