<?php

namespace App\MyMall\Classes;

use App\Models\PricingModel;

class PriceDecisions extends PricingModel
{
    public function shouldImport(array $sdProduct)
    {
        // ... query the DB

        if(empty($sdProduct))
            return false;

        return true;
    }
}
