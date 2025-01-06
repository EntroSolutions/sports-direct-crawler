<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SkipBrandRuleRequest;
use App\Models\Brand;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SkipBrandRuleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SkipBrandRuleCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\SkipBrandRule');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/skipbrandrule');
        $this->crud->setEntityNameStrings('skip brand rule', 'skip brand rules');

        $this->crud->denyAccess(['create', 'delete']);
    }

    protected function setupListOperation()
    {
        CRUD::column('id');

        $this->crud->addColumn([
            'name'      => 'brand',
            'type'      => 'relationship',
            'label'     => 'Brand',
            'attribute' => 'name',
        ]);

        CRUD::column('skip')->type('boolean');

        // dropdown filter
        $this->crud->addFilter([
            'name'  => 'skip',
            'type'  => 'dropdown',
            'label' => 'Skip'
        ], [
            0 => 'No',
            1 => 'Yes',
        ], function($value) { // if the filter is active
             $this->crud->addClause('where', 'skip', $value);
        });
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(SkipBrandRuleRequest::class);


        $this->crud->addField([
            // relationship
            'type'       => "relationship",
            'name'       => 'brand', // the method on your model that defines the relationship
            'label'      => "Brand name",
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);

        CRUD::field('skip');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
