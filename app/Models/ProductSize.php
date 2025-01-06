<?php

namespace App\Models;

use App\MyMall\Traits\CurrencyTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSize extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use HasTranslations;
    use CurrencyTrait;

    protected $translatable = ['name'];

    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor()
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function colorImages()
    {
        return $this->hasMany(ColorImage::class);
    }

    public function scopeByMymallId($query, $mymall_id)
    {
        return $query->where('mymall_id', $mymall_id);
    }

    // Accessor for backpack list image
    public function getColorImageAttribute()
    {
        return $this->productColor && $this->productColor->colorImages->count()
            ? $this->productColor->colorImages()->first()->image
            : null;
    }

    public function getColorImageUrl()
    {
        return asset($this->getColorImageAttribute());
    }

    public function getBackpackImageThumbHtml()
    {
        return '<img src="' . $this->getColorImageUrl() . '" width="50px" height="50px" />';
    }

    public function scopeShouldBeDisabled($query)
    {

    }

    public function getPriceForCsCart(bool $userOldPrice = false)
    {

        $price = $userOldPrice ? $this->price_old : $this->price;

        if($price == 0)
            return $price;

        $discountPercent = $this->discount_amount > 0 && $this->price_old > 0
            ? $this->discount_amount / $this->price_old * 100
            : 0;

        $pricingModel = PricingModel::filter($this->price, $discountPercent)->first();
        $priceIncreasePercent = !$userOldPrice
            ? $pricingModel->price_increase_percent
            : $pricingModel->list_price_increase_percent;

        $priceCalculated = round(
            $this->convert($price +  ($price * $priceIncreasePercent / 100)),
            0
            ) . '.' .$pricingModel->round;


        return $priceCalculated;
    }

}
