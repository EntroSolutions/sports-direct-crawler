<?php

namespace App\MyMall\Traits;

use App\Models\Currency;

trait CurrencyTrait
{
    public function convert($price, $toCurrency = 'EUR')
    {
        $gbpRate = Currency::byCode($toCurrency)->first()->rate;

        return $price * $gbpRate;
    }
}
