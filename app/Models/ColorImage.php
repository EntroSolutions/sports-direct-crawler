<?php

namespace App\Models;

use App\MyMall\Traits\CurlTrait;
use App\MyMall\Traits\ProductMediaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ColorImage extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use CurlTrait;
    use ProductMediaTrait;

    protected $guarded = ['id'];

    public function productColor()
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function storeImage($value)
    {

        if(request()->is('admin/*') && ! Str::contains($value, 'sportsdirect')) {

            $attribute_name = "image";
            // or use your own disk, defined in config/filesystems.php
            $disk = config('backpack.base.root_disk_name');
            // destination path relative to the disk above
            $destination_path = "storage/app/public/images/products/" . $this->productColor->product->id . '/colors';

            // if the image was erased
            if ($value == null) {
                // delete the image from disk
                \Storage::disk($disk)->delete($this->{$attribute_name});

                // set null in the database column
                $this->attributes['image'] = null;
            }

            // if a base64 was sent, store it in the db
            if (Str::startsWith($value, 'data:image')) {
                // 0. Make the image
                $image = \Image::make($value)->encode('jpg', 90);

                // 1. Generate a filename.
                $filename = $this->productColor->sd_color_id . '_' . now()->timestamp . '.jpg';

                // 2. Store the image on disk.
                \Storage::disk($disk)->put($destination_path . '/' . $filename, $image->stream());

                // 3. Delete the previous image, if there was one.
                \Storage::disk($disk)->delete($this->{$attribute_name});

                // 4. Save the public path to the database
                // but first, remove "public/" from the path, since we're pointing to it
                // from the root folder; that way, what gets saved in the db
                // is the public URL (everything that comes after the domain name)
                $public_destination_path = Str::replaceFirst('app/public/', '', $destination_path);
                $this->attributes[$attribute_name] = $public_destination_path . '/' . $filename . '?v=' . now()->timestamp;
                return $this->attributes[$attribute_name];
            }
        } else{

            $colorImageId = $this->id
                ? $this->id
                : (ColorImage::latest()->first()
                    ? ColorImage::latest()->first()->id + 1
                    : 1);

            $imageContents = $this->request($value);

            $extension = File::extension($value);
            $path = 'images/products/'.$this->productColor->product_id.'/colors/.' . $colorImageId . '/' . $this->productColor->sd_color_id . '.' . $extension;

            Storage::disk('public')->delete($path);
            Storage::disk('public')->put($path, $imageContents);

            $this->attributes['image'] = 'storage/' . $path  . '?v=' . now()->timestamp;
        }
    }

    public function setImageAttribute($value)
    {

        if(request()->is('admin/*') && ! Str::contains($value, 'sportsdirect')) {

            return $value;

        } else{

            $colorImageId = $this->id
                ? $this->id
                : (ColorImage::latest()->first()
                    ? ColorImage::latest()->first()->id + 1
                    : 1);

            $imageContents = $this->request($value);

            $extension = File::extension($value);
            $exploded = explode('/', $value);
            $path = 'images/products/'.$this->productColor->product_id.'/colors/'.$this->productColor->sd_color_id.'/' . end($exploded);

            Storage::disk('public')->delete($path);
            Storage::disk('public')->put($path, $imageContents);

            $imageInfo = getimagesize(public_path('storage/' . $path));

            // Check if the image is in AVIF format and convert it to jpg
            if ($imageInfo['mime'] == 'image/avif') {
                $this->convertAvifImageToJpg(public_path('storage/' . $path), 100);
            } elseif ($imageInfo['mime'] == 'image/webp') {
                $this->convertWebpImageToJpg(public_path('storage/' . $path), 100);
            }

            $this->attributes['image'] = 'storage/' . $path;
        }
    }

    public function getImageAttribute($value)
    {
        if( !Str::startsWith($value, 'http') ){
            return asset($value);
        }

        return $value;
    }
}
