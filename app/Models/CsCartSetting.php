<?php

namespace App\Models;

use App\Http\Controllers\Admin\CsCartSettingCrudController;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class CsCartSetting extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'cs_cart_settings';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    const CS_FEATURES = [
        'color' => 'color',
        'size' => 'size',
        'brand' => 'brand',
        'vendor' => 'vendor',
        'tax' => 'tax',
        'delivery' => 'delivery',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public static function getCsColorFeatureId()
    {
        return self::byType('color')->first()->feature_id;
    }

    public static function getTaxFeatureIds()
    {
        return self::byType('tax')->pluck('feature_id');
    }

    public static function getCsSizeFeatureId()
    {
        return self::byType('size')->first()->feature_id;
    }

    public static function getCsBrandFeatureId()
    {
        return self::byType('brand')->first()->feature_id;
    }

    public function getFeaturesColumnValue()
    {
        return (new CsCartSettingCrudController())->getCsCartFeatures($this->feature_type)[$this->feature_id];
    }

    public function getFeaturesColumnVariantVaue()
    {
        if ($this->feature_variant_id)
            return (new CsCartSettingCrudController())->getCsCartFeatureById($this->feature_id)[$this->feature_variant_id];

        return null;
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeByName($query, string $featureName)
    {
        return $query->where('feature_name', $featureName);
    }

    public function scopeByType($query, string $featurType)
    {
        return $query->where('feature_type', $featurType);
    }
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
