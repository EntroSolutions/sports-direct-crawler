<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingModel extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeFilter($query, $price, $discountPercent)
    {
        return $query->where('price_to', '>=', $price)
            ->where('sd_discount', '>=', $discountPercent)
            ->orderBy('price_to', 'asc')
            ->orderBy('sd_discount', 'asc')
            ->limit(1);
    }
}
