<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Translate;
use Illuminate\Console\Command;

class PushProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mm:push-new-products {categoryId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push products to CsCart';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $categoryId =  $this->argument('categoryId');

        Product::select('products.id', 'product_categories.category_id', 'ps.product_id')
            ->join('product_sizes as ps', 'ps.product_id', 'products.id')
            ->join('product_categories', 'product_categories.product_id', 'products.id')
            ->when($categoryId, function($q) use($categoryId){
                return $q->where('product_categories.category_id', $categoryId);
            })
            ->applySkipFilters()
            ->orderBy('products.id')
            ->groupBy('products.id')
            ->chunkById(100, function($products){

                foreach ($products as $product){

                    \App\Jobs\PushProducts::dispatch($product->id)
                        ->onQueue(config('queue.products_cscart_queue'));

                }

            }, 'products.id', 'id');
    }
}
