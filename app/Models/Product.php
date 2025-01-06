<?php

namespace App\Models;

use App\MyMall\Traits\CurlTrait;
use App\MyMall\Traits\ProductMediaTrait;
use App\MyMall\Traits\ProductSkipTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use CurlTrait;
    use HasTranslations;
    use ProductSkipTrait;
    use ProductMediaTrait;


    protected $translatable = [
        'name',
        'description'
    ];

    protected $guarded = ['id'];

    public function skipProductRule()
    {
        return $this->hasOne(SkipProductRule::class, 'sd_product_id', 'sd_product_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class);
    }

    public function sizes()
    {
        return $this->hasMany(ProductSize::class)
            ->orderBy('price', 'asc');
    }

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function category()
    {
        return $this->hasOneThrough(Category::class,
            ProductCategory::class,
            'product_id',
            'id',
        'id',
        'category_id');
    }

    public function scopeByMyMallId($query, $mymall_id)
    {
        return $query->where('mymall_id', $mymall_id);
    }

    public function setImageAttribute($value){

        if(request()->is('admin/*') && ! Str::contains($value, 'sportsdirect')){

            $attribute_name = "image";
            // or use your own disk, defined in config/filesystems.php
            $disk = config('backpack.base.root_disk_name');
            // destination path relative to the disk above
            $destination_path = "storage/app/public/images/products/" . $this->id;

            // if the image was erased
            if ($value==null) {
                // delete the image from disk
                \Storage::disk($disk)->delete($this->{$attribute_name});

                // set null in the database column
                $this->attributes[$attribute_name] = null;
            }

            // if a base64 was sent, store it in the db
            if (Str::startsWith($value, 'data:image'))
            {
                // 0. Make the image
                $image = \Image::make($value)->encode('jpg', 90);

                // 1. Generate a filename.
                $filename = $this->sd_product_id.'.jpg';

                // 2. Store the image on disk.
                \Storage::disk($disk)->put($destination_path . '/' . $filename, $image->stream());

                // 3. Delete the previous image, if there was one.
                \Storage::disk($disk)->delete($this->{$attribute_name});

                // 4. Save the public path to the database
                // but first, remove "public/" from the path, since we're pointing to it
                // from the root folder; that way, what gets saved in the db
                // is the public URL (everything that comes after the domain name)
                $public_destination_path = Str::replaceFirst('app/public/', '', $destination_path);
                $this->attributes[$attribute_name] = asset($public_destination_path.'/'.$filename . '?v=' . now()->timestamp);
            }

        } else {

            $productId = $this->id
                ? $this->id
                : (Product::latest()->first()
                    ? Product::latest()->first()->id + 1
                    : 1);

            $imageContents = $this->request($value);

            $extension = File::extension($value);
            $path = 'images/products/' . $productId . '/' . $this->sd_product_id . '.' . $extension;

            Storage::disk('public')->delete($path);
            Storage::disk('public')->put($path, $imageContents);

            $imageInfo = getimagesize(public_path('storage/' . $path));

            // Check if the image is in AVIF format and convert it to jpg
            if (isset($imageInfo['mime']) && $imageInfo['mime'] == 'image/avif') {
                $this->convertAvifImageToJpg(public_path('storage/' . $path), 100);
            } elseif (isset($imageInfo['mime']) && $imageInfo['mime'] == 'image/webp') {
                $this->convertWebpImageToJpg(public_path('storage/' . $path), 100);
            }

            $this->attributes['image'] = 'storage/' . $path;

            //$this->attributes['image'] = $value; // Sportsdirect url
        }

    }

    public function getProductImageUrl()
    {
        return asset($this->image);
    }

    public function getBackpackImageThumbHtml()
    {
        return '<img src="'.$this->getProductImageUrl().'" width="50px" height="50px" />';
    }
}
