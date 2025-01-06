<?php

namespace App\Jobs;

use App\Models\Category;
use App\MyMall\Classes\ParseCategory;
use App\MyMall\Traits\CurlTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;

class ProcessCategory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use CurlTrait;


    private Category $category;
    private mixed $sdCategoryId = null;
    private Browser $browser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        dump('Crawling '.$this->category->url);

        try {

            $this->category->sync_started_at = now();
            $this->category->sync_ended_at = null;
            $this->category->sync_message = '<span class="bg-info">Syncing category and processing products ...</span>';
            $this->category->save();

            // No mymall_id set but has been crawled and sd_category is set. Skip category
            if($this->category->sd_category_id && !$this->category->mymall_id) {

                $this->category->sync_ended_at = now();
                $this->category->sync_message = '<span class="bg-danger">ERROR: MyMall Category ID is not set (NOT MAPPED)!</span>';
                $this->category->save();
                return;

            } else {

                $this->processCategory();

                $this->category->sync_ended_at = now();
                $this->category->sync_message = '<span class="bg-success">Completed. All product jobs are sent into the queue at ' . now()->toDateTimeString().'</span>';
                $this->category->save();

            }


        } catch (\Exception $e){
            info($e->getMessage());
//            dd($e->getMessage());
            $this->category->sync_ended_at = now();
            $this->category->sync_message = '<span class="bg-danger">ERROR: ' . $e->getMessage() .'</span>';
            $this->category->save();

        }


        app('queue.worker')->shouldQuit  = 1;

        return true;
    }

    public function processCategory()
    {
        $this->crawlCategory();
        $this->category->refresh();

        $page = 1;

        $productIds = [];

        while($page > 0){

            usleep(mt_rand(300000, 1000000)); // 0.3s to 1s

            $sdCategoryId = Str::replace('SDBG_', 'SD_', $this->category->sd_category_id);

            $getProductsUrl = 'https://sportsdirect.com/API/productlist/v1/getforcategory\?categoryId\='.$sdCategoryId.'\&page\='.$page.'\&productsPerPage\=60';
            $resultJson = $this->request($getProductsUrl);
            $result = json_decode($resultJson);

            if(!$result || !$result->products)
                break;

            // Set product ids first
            foreach ($result->products as $product) {
                $productIds[$product->productId] = $product->productId;
            }

            // break while of no other pages
            if($result->numberOfPages <= $page)
                break;

            $page++;

        }

        foreach ($productIds as $productId){
            dispatch(new ProcessProduct($productId, $this->category->id))
                ->onQueue(config('queue.products_queue'));
        }

    }

    private function crawlCategory()
    {
        ParseCategory::instance()->crawl($this->category);
    }
}
