<?php

namespace App\MyMall\Classes;

use App\MyMall\Traits\Singleton;

class Product extends ParseProduct
{
    use Singleton;

    protected $product;

    public function getProduct( int $id, int $size, int $color)
    {
        $this->product = parent::getById($id);

        return $this;
    }

    public function isAvailable()
    {

    }

    public function checkPrice($our_price)
    {

    }

    public function checkSize()
    {

    }
}
