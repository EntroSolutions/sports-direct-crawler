<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\ProductColor;
use App\MyMall\Filter;
use App\Observers\ProductColorObserver;
use App\Observers\ProductObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Filther::class, function () {
            return new Filter();
        });

//        if(env('APP_ENV') == 'production'){
//            \URL::forceScheme('https');
//        }

        // Observers
        Product::observe(ProductObserver::class);
        ProductColor::observe(ProductColorObserver::class);
    }
}
