<?php

namespace App\MyMall\Filters;

use App\MyMall\Traits\Singleton;

class FilterCategory implements FilterInterface
{
    use Singleton;

    public function shouldSkip(mixed $category) : bool
    {
        return $category->skipCategoryRule->skip;
    }
}
