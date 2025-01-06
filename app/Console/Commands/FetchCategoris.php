<?php

namespace App\Console\Commands;

use App\Jobs\ProccessCategory;
use App\Jobs\ProcessCategory;
use App\Models\Category;
use App\Models\SkipCategoryRule;
use App\MyMall\Classes\ImportCategory;
use App\MyMall\Filter;
use App\MyMall\Traits\CurlTrait;
use Illuminate\Console\Command;

class FetchCategoris extends Command
{
    use CurlTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mm:fetch-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //$categories = 'https://sportsdirect.com/API/productlist/v1/getforcategory?categoryId=SDBG_MENSCLOFLEECES&page=1&productsPerPage=100';

//        exec('torify curl ifconfig.me 2>/dev/null 46.165.221.166');

        $categories = 'https://bg.sportsdirect.com/sitemap-category-department.xml';

        $xml = $this->request($categories);

        $parsedXml = simplexml_load_string($xml) or die("Error: Cannot create categories XML object");

        $filter = app()->make(\App\MyMall\Filters\Filter::class);

        $i = 0;

        foreach ($parsedXml->url as $key => $categoryXml)
        {
            $category = ImportCategory::instance()->import($categoryXml);

            $category->refresh();

            if ($filter->category()->shouldSkip($category)) continue;

            dispatch(new ProcessCategory($category))
                ->onQueue(config('queue.categories_queue'));

            $i++;
        }
    }
}
