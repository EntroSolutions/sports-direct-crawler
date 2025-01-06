<?php

namespace App\Models;

use App\MyMall\Traits\CurlTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductColor extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use CurlTrait;
    use HasTranslations;

    protected $translatable = ['name'];

    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function getProductImageUrlAttribute()
    {
        return $this->colorImages()->count()
            ? $this->colorImages()->first()->image
            : null;
    }

    public function colorImages()
    {
        return $this->hasMany(ColorImage::class);
    }
}
