<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkipProductRule extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'sd_product_id', 'sd_product_id');
    }

    public function getProductImageAttribute()
    {
        return $this->product
            ? $this->product->image
            : null;
    }

    public function getProductImageUrl()
    {
        return $this->product
            ? asset($this->product->image)
            : null;
    }

    public function getBackpackImageThumbHtml()
    {
        return '<img src="'.$this->getProductImageUrl().'" width="50px" height="50px" />';
    }
}
