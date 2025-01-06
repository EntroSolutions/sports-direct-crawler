<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PricingModelRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PricingModelCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PricingModelCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\PricingModel');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/pricingmodel');
        $this->crud->setEntityNameStrings('pricing model', 'pricing models');

        $this->crud->denyAccess('create');
    }

    protected function setupListOperation()
    {
        CRUD::column('id');
        CRUD::column('price_to')->label('SD price in £');
        CRUD::column('price_increase_percent')->label('Price increase in %');
        CRUD::column('list_price_increase_percent')->label('List Price increase in %');
        CRUD::column('sd_discount')->label('SD discount in < %');
        CRUD::column('round')->label('Round <small>99</small>');
        CRUD::column('created_at');
        CRUD::column('updated_at');
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(PricingModelRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
