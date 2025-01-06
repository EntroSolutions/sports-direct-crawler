<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    use CrudTrait;

    protected $guarded = ['id'];

    public static function firstOrCreateByName($name){

        return self::firstOrCreate(
            ['name' => $name]
        );

    }

    public function skipBrandRule()
    {
        return $this->hasOne(SkipBrandRule::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
