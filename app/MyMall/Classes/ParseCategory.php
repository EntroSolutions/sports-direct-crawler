<?php

namespace App\MyMall\Classes;

use App\Models\Category;
use App\MyMall\Traits\CurlTrait;
use App\MyMall\Traits\Singleton;
use DuskCrawler\Dusk;
use Laravel\Dusk\Browser;
use Symfony\Component\DomCrawler\Crawler;

class ParseCategory extends Parse
{

    use Singleton;
    use CurlTrait;

    public function crawl(Category $category)
    {

        $response = $this->request($category->url);

        $crawler = new Crawler($response);

        $titleNode = $crawler->filter('#lblCategoryHeader');

        if (!count($titleNode)){

            dump('SKIPPING: No category title found');
            throw new \Exception('SKIPPING: No category title found');

            return true;
        }

        $title = $titleNode->text();

        $idNode = $crawler->filter('#productlistcontainer');

        if (!count($idNode)){

            dump('SKIPPING: No category ID found');
            throw new \Exception('SKIPPING: No category ID found');

            return true;
        }
        $sdCategoryId = $idNode->attr('data-category');


        $category->name = $title;
        $category->sd_category_id = $sdCategoryId;
        $category->save();
    }

    // Not used
    public function crawlWithDusk(Category $category)
    {
        $dusk = new Dusk('search-packagist');

        $dusk->headless()->disableGpu()->noSandbox();
//        $dusk->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
        $dusk->userAgent('Mozilla/5.0 (Linux; Android 7.0; SM-G930V Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.125 Mobile Safari/537.36');

        $dusk->start();

        try {

            $dusk->browse(function (Browser $browser) use ($category) {
                $browser->visit($category->url);

                $title = $browser->resolver
                    ->findOrFail('#lblCategoryHeader')
                    ->getText();

                $sdCategoryId = $browser->resolver
                    ->findOrFail('#productlistcontainer')
                    ->getAttribute('data-category');

                // $browser->screenshot('success_get_cat');

                $category->name = $title;
                $category->sd_category_id = $sdCategoryId;
                $category->save();

            });
        } catch (\Exception $e) {
            dump($e->getMessage());
        }

        $dusk->stop();
        Dusk::closeAll();
    }
}
