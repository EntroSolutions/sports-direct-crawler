<?php

namespace App\Models;

use App\MyMall\Traits\TranslateTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use HasTranslations;
    use TranslateTrait;

    protected $translatable = ['name'];

    protected $guarded = ['id'];

    public static function firstOrCreateByName($name, $category_id = null)
    {
        return self::firstOrCreate(
            [
                'name->en' => $name,
                'category_name' => $category_id
                    ? Category::find($category_id)?->name
                    : $category_id
            ],
            [
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

    public function getNameWithCategoryAttribute()
    {
        return '<b>'.$this->name.'</b>' . ' <span class="font-sm">('.$this->category_name.')</span>';
    }

    public function csCartSetting()
    {
        return $this->belongsTo(CsCartSetting::class);
    }
}
