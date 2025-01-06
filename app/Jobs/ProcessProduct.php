<?php

namespace App\Jobs;

use App\Models\Product;
use App\MyMall\Classes\ImportProduct;
use App\MyMall\Classes\ParseProduct;
use App\MyMall\Filters\Filter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public mixed $product_id = null;
    public mixed $category_id = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product_id, $category_id)
    {
        $this->product_id = $product_id;
        $this->category_id = $category_id;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
//        $urlSingleProduct = https://sportsdirect.com/api/productlist/v1/product/201036;

        $theirProduct = ParseProduct::instance()->createOrUpdateProduct($this->product_id, $this->category_id);
        if (empty($theirProduct))
            return true;

        $productFilterClass = app()->make(Filter::class)->product();

        if ($productFilterClass->shouldSkip($theirProduct))
            return true;

        // Get the updated product object from shouldSkip method
        $theirProduct = $productFilterClass->product;

        $createdOrUpdatedProduct =  ImportProduct::instance()->import($theirProduct, $this->category_id);


        PushProducts::dispatch($createdOrUpdatedProduct->id)
            ->onQueue(config('queue.products_cscart_queue'));

        app('queue.worker')->shouldQuit  = 1;

        return true;
    }
}
