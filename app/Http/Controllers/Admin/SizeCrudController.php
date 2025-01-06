<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SizeRequest;
use App\Models\CsCartSetting;
use App\Models\ProductColor;
use App\Models\Size;
use App\MyMall\Traits\BackpackAlert;
use App\MyMall\Traits\CsCartApi;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class SizeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SizeCrudController extends CrudController
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
        $this->crud->setModel('App\Models\Size');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/size');
        $this->crud->setEntityNameStrings('size', 'sizes');

        $this->crud->denyAccess('create');
    }

    protected function setupListOperation()
    {
        CRUD::column('id');

        $this->crud->addColumn([
            'name' => 'name_with_category',
            'escaped' => false,
            'limit' => 500,
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('name', 'like', '%'.$searchTerm.'%');
            }
        ]);

        $this->crud->addColumn([   // 1-n relationship
            'label'       => "Mymall Size ID", // Table field heading
            'type'        => "text",
            'name'        => 'mymall_id', // the field that contains the ID of that connected entity
        ]);
        CRUD::column('created_at');
        CRUD::column('updated_at');

        // simple filter
        $this->crud->addFilter([
            'type'  => 'text',
            'name'  => 'category_name',
            'label' => 'Category name',
        ],
            false,
            function($value) { // if the filter is active
                $this->crud->query->where('category_name', 'like', '%'.$value.'%');
            });
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(SizeRequest::class);

        $sizeId = Route::getCurrentRoute()->parameter('id');
        $size = Size::findOrFail($sizeId);

        CRUD::field('name')
            ->type('text')
            ->hint('For category: <b>'.$size->category_name.'</b>');

        $this->crud->addField([
            'name' => 'cs_cart_setting_id',
            'label' => 'Mapped features',
            'type' => 'select2_from_array',
            'options' => CsCartSetting::where('feature_type', 'size')->pluck('feature_name', 'id'),
        ]);

        $this->crud->addField([   // 1-n relationship
            'label'       => "Mymall Size", // Table field heading
            'type'        => "select2_from_array_cs_sizes",
            'name'        => 'mymall_id', // the field that contains the ID of that connected entity
            'depending_select' => 'cs_cart_setting_id',
            'options'     => $size->cs_cart_setting_id
                ? $this->getCsCartSizes($size->cs_cart_setting_id)
                : []
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function getCsCartSizes($csCartSettingId = null)
    {
        if(request()->has('setting_id'))
            $setting = CsCartSetting::find(request('setting_id'));
        else
            $setting = CsCartSetting::find($csCartSettingId);

        if(!$setting){
            $this->alert('You have sizes that are not mapped', 'You should map all of them','danger');
            return [];
        }

        $url = config('cscart.api_base') . 'features/' . $setting->feature_id.'&items_per_page=50000';

        $csFeatures = json_decode($this->getRequest($url), true);

        if(!isset($csFeatures['variants'])){
            return [];
        }

        $sizes = Arr::pluck($csFeatures['variants'], 'variant', 'variant_id');

        return $sizes;
    }
}
