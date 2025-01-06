<?php

namespace App\MyMall\Filters;

interface FilterInterface
{
    public function shouldSkip( mixed $identifier);
}
