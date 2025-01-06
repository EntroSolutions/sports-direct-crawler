<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SkipProductRuleRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SkipProductRuleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SkipProductRuleCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\SkipProductRule');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/skipproductrule');
        $this->crud->setEntityNameStrings('skip product sku rule', 'skip product sku rules');
    }

    protected function setupListOperation()
    {
        CRUD::column('id');

        $this->crud->addColumn([
            'name'      => 'product_image', // The db column name
            'label'     => 'Image', // Table column heading
            'type'      => 'image',
            //             'prefix' => 'folder/subfolder/',
            // image from a different disk (like s3 bucket)
            // 'disk'   => 'disk-name',
            // optional width/height if 25px is not ok with you
            'height' => '50px',
            'width'  => '50px',
        ]);

        $this->crud->addColumn([
            // any type of relationship
            'name'         => 'product', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Product', // Table column heading
            // OPTIONAL
            // 'entity'    => 'tags', // the method that defines the relationship in your Model
             'attribute' => 'name', // foreign key attribute that is shown to user
            // 'model'     => App\Models\Category::class, // foreign key model
        ]);

        $this->crud->addColumn([
            // any type of relationship
            'name'         => 'product', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Product', // Table column heading
            // OPTIONAL
            // 'entity'    => 'tags', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            // 'model'     => App\Models\Category::class, // foreign key model
        ]);

        $this->crud->addColumn([
            'name' => 'sd_product_id',
            'label' => 'Skip SKU'
        ]);

        // simple filter
        $this->crud->addFilter([
            'type'  => 'text',
            'name'  => 'product_name',
            'label' => 'Product name',
        ],
            false,
            function($value) { // if the filter is active
                $this->crud->query = $this->crud->query->whereHas('product', function ($query) use ($value) {
                    $query->where('name', 'like', '%'.$value.'%');
                });
            });
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(SkipProductRuleRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
