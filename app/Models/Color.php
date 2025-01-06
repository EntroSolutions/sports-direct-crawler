<?php

namespace App\Models;

use App\MyMall\Traits\TranslateTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use HasTranslations;
    use TranslateTrait;

    protected $translatable = ['name'];

    protected $guarded = ['id'];

    public static function firstOrCrateByName($name, $sdColorId)
    {
        return self::firstOrCreate(
            [
                'name->en' => $name,
            ],
            [
                'sd_color_id' => $sdColorId,
                'name' => [
                    // Translations
                    'en' => $name,
                    'bg_BG' => self::translateStringStatic($name, 'bg'),
                    'el' => self::translateStringStatic($name, 'el'),
                    'ro' => self::translateStringStatic($name, 'ro'),
                ],
            ]
        );
    }
}
