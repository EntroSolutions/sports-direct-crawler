<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BrandRequest;
use App\Models\Color;
use App\Models\CsCartSetting;
use App\MyMall\Traits\CsCartApi;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

/**
 * Class BrandCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BrandCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use CsCartApi;

    public function setup()
    {
        $this->crud->setModel('App\Models\Brand');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/brand');
        $this->crud->setEntityNameStrings('brand', 'brands');
        $this->crud->denyAccess(['create', 'delete']);
    }

    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID');
        CRUD::column('name')->label('Name');
        CRUD::column('mymall_id')->label('Mymall ID');
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(BrandRequest::class);

        $this->crud->addField([
            'name' => 'name',
            'label' => '',
        ]);

        $csBrandFeatures = $this->getCsCartBrands();

        $this->crud->addField([   // 1-n relationship
            'label'       => "Mymall Brand", // Table field heading
            'type'        => "select2_from_array",
            'name'        => 'mymall_id', // the field that contains the ID of that connected entity
            'options'     => $csBrandFeatures
        ]);

    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function getCsCartBrands()
    {
        $brandFeature = CsCartSetting::byType('brand')->first();

        $url = config('cscart.api_base') . 'features/'.$brandFeature->feature_id.'&items_per_page=50000';

        $csFeatures = json_decode($this->getRequest($url), true);

        if(!isset($csFeatures['variants'])){
            return [];
        }

        $brands = Arr::pluck($csFeatures['variants'], 'variant', 'variant_id');

        return $brands;
    }
}
