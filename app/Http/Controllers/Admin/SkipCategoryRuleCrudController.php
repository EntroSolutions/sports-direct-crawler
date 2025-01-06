<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SkipCategoryRuleRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SkipCategoryRuleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SkipCategoryRuleCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\SkipCategoryRule');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/skipcategoryrule');
        $this->crud->setEntityNameStrings('skip category rule', 'skip category rules');
        $this->crud->denyAccess(['create', 'delete']);
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'category_id',
            'label' => 'Category ID'
        ]);

        $this->crud->addColumn([
            // any type of relationship
            'name'         => 'category', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Category', // Table column heading
            // OPTIONAL
//             'entity'    => 'name', // the method that defines the relationship in your Model
             'attribute' => 'name', // foreign key attribute that is shown to user
            // 'model'     => App\Models\Category::class, // foreign key model
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('category', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);

        $this->crud->addColumn([
            'name' => 'skip',
            'type' => 'boolean',
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(SkipCategoryRuleRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
