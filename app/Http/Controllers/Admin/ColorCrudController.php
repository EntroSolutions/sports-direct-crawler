<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ColorRequest;
use App\Models\Color;
use App\Models\CsCartSetting;
use App\MyMall\Traits\BackpackAlert;
use App\MyMall\Traits\CsCartApi;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class ColorCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ColorCrudController extends CrudController
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
        $this->crud->setModel('App\Models\Color');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/color');
        $this->crud->setEntityNameStrings('color', 'colors');

        $this->crud->denyAccess('create');
    }

    protected function setupListOperation()
    {
        CRUD::column('id');
        CRUD::column('name');
        $this->crud->addColumn([   // 1-n relationship
            'label'       => "Mymall Color", // Table field heading
            'type'        => "select_from_array",
            'name'        => 'mymall_id', // the field that contains the ID of that connected entity
            'options'     => $this->getCsCartColors(),
        ]);
        CRUD::column('created_at');
        CRUD::column('updated_at');
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(ColorRequest::class);

        CRUD::field('name');

        $csColorFeatures = $this->getCsCartColors();

        $this->crud->addField([   // 1-n relationship
            'label'       => "Mymall Color", // Table field heading
            'type'        => "select2_from_array",
            'name'        => 'mymall_id', // the field that contains the ID of that connected entity
            'options'     => $csColorFeatures,
            'default' => isset(array_flip($csColorFeatures)[Color::where('id', Route::getCurrentRoute()->parameter('id'))->first()->name])
            ? array_flip($csColorFeatures)[Color::where('id', Route::getCurrentRoute()->parameter('id'))->first()->name]
                : ''
        ]);
        CRUD::field('created_at');
        CRUD::field('updated_at');

    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function getCsCartColors()
    {
        $colorFeature = CsCartSetting::where('feature_type', 'color')->first();

        if(!$colorFeature){
            $this->alert('You have colors that are not mapped', 'You should map all of them','danger');
            return [];
        }

        $url = config('cscart.api_base') . 'features/'.$colorFeature->feature_id.'&items_per_page=50000';

        $csFeatures = json_decode($this->getRequest($url), true);

        if(!isset($csFeatures['variants'])){
            return [];
        }

        $colors = Arr::pluck($csFeatures['variants'], 'variant', 'variant_id');

        return $colors;
    }
}
