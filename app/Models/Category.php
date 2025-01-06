<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use HasTranslations;

    protected $casts = [
        'subcategories' => 'array'
    ];

    protected $translatable = ['name'];

    protected $guarded = ['id'];

    public function skipCategoryRule()
    {
        return $this->hasOne(SkipCategoryRule::class);
    }

    public function crawlNowButton()
    {
        return '<a href="'.route('category.crawlNow', $this->id).'" class="btn btn-sm btn-success"><i class="las la-cog"></i> Crawl now</a>';
    }
}
