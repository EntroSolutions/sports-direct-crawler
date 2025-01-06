<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CsCartSettingRequest;
use App\Models\CsCartSetting;
use App\Models\Size;
use App\MyMall\Traits\BackpackAlert;
use App\MyMall\Traits\CsCartApi;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class CsCartSettingCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CsCartSettingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use CsCartApi;
    use BackpackAlert;

    public function setup()
    {
        $this->crud->setModel('App\Models\CsCartSetting');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/cscartsetting');
        $this->crud->setEntityNameStrings('cscart setting', 'cscart settings');
    }

    protected function setupListOperation()
    {

        $this->crud->addColumn([
            'name' => 'feature_name',
            'label' => 'Feature name (internal)',
            'type' => 'text',
        ]);

        $this->crud->addColumn([
            'name' => 'feature_type',
            'label' => 'CsCart Feature Type',
            'key' => 0,
            'type' => 'select_from_array',
            'options' => CsCartSetting::CS_FEATURES
        ]);

        $this->crud->addColumn([
            'name' => 'feature_id',
            'label' => 'CsCart Feature ID',
            'type' => 'model_function',
            'function_name' => 'getFeaturesColumnValue',
        ]);

        $this->crud->addColumn([
            'name' => 'feature_variant_id',
            'label' => 'CsCart Feature Variant ID',
            'type' => 'model_function',
            'function_name' => 'getFeaturesColumnVariantVaue',
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(CsCartSettingRequest::class);

        $settingId = Route::getCurrentRoute()->parameter('id');
        $csSetting = null;
        if($settingId){
            $csSetting = CsCartSetting::findOrFail($settingId);
        }

        $this->crud->addField([
            'name' => 'feature_name',
            'label' => 'Feature Name (internal)',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'feature_type',
            'label' => 'CsCart Feature Type',
            'type' => 'select2_from_array',
            'options' => CsCartSetting::CS_FEATURES,
        ]);

        $this->crud->addField([
            'name' => 'feature_id',
            'label' => 'CsCart Feature ID',
            'type' => 'select2_from_array_cs_settings',
            'depending_select' => 'feature_type',
            'options' => $csSetting && $csSetting->feature_type
                ? $this->getCsCartFeatures($csSetting->feature_type)
                : [],
        ]);

        if($csSetting && $csSetting->feature_type == 'delivery'){
            $this->crud->addField([   // Hidden
                'name' => 'feature_variant_id',
                'type' => 'select2_from_array',
                'options' => $this->getCsCartFeatureById($csSetting->feature_id),
                'wrapper'   => [
                    'id' => 'delivery_options'
                ]
            ]);
        } else {
            $this->crud->addField([   // Hidden
                'name' => 'feature_variant_id',
                'type' => 'hidden',
                'wrapper'   => [
                    'id' => 'delivery_options'
                ]
            ]);
        }
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function getCsCartFeatures($csCartSettingType = null)
    {
        $type = request()->has('feature_type')
            ? request()->feature_type
            : $csCartSettingType;

        $url = match ($type) {
            'vendor' => config('cscart.api_base') . 'vendors',
            'tax' => config('cscart.api_base') . 'taxes',
            default => config('cscart.api_base') . 'features&purpose[]=group_variation_catalog_item&purpose[]=organize_catalog&items_per_page=50000',
        };

        $csFeaturesResponse = json_decode($this->getRequest($url), true);

        if(empty($csFeaturesResponse['features']) && empty($csFeaturesResponse['vendors']) && empty($csFeaturesResponse['taxes'])){
            $this->alert('Not able to get the features for mapping', 'Please contact a developer','danger');
            return [];
        }

        $csFeatures = match($type){
            'vendor' => $csFeaturesResponse['vendors'],
            'tax' => $csFeaturesResponse['taxes'],
            default => $csFeaturesResponse['features']
        };

        $features = [];

        if($type == 'vendor'){

            foreach (array_chunk($csFeatures, 100) as $chunk) {

                foreach ($chunk as $csFeature) {
                    $features[$csFeature['company_id']] = $csFeature['company'] . ' | ' . $csFeature['email'];
                }
            }

        }
        else if($type == 'tax'){

            foreach (array_chunk($csFeatures, 100) as $chunk) {

                foreach ($chunk as $csFeature) {
                    $features[$csFeature['tax_id']] = $csFeature['tax'] . ' | regnumber: ' . $csFeature['regnumber'];
                }
            }

        }
        else {

            foreach (array_chunk($csFeatures, 100) as $chunk) {

                foreach ($chunk as $csFeature) {
                    $features[$csFeature['feature_id']] = $csFeature['description'] . ' | ' . $csFeature['purpose'];
                }
            }
        }

        return $features;
    }

    public function getCsCartFeatureById($csFeatureId = null)
    {
        if($csFeatureId)
            $settingId = $csFeatureId;
        else
            $settingId = request('setting_id');

        if(!$settingId){
            $this->alert('You have sizes that are not mapped', 'You should map all of them','danger');
            return [];
        }

        $url = config('cscart.api_base') . 'features/' . $settingId.'&items_per_page=50000';

        $csFeatures = json_decode($this->getRequest($url), true);

        if(!isset($csFeatures['variants'])){
            return [];
        }

        $featureVariants = Arr::pluck($csFeatures['variants'], 'variant', 'variant_id');

        return $featureVariants;
    }
}
