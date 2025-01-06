<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductSizeRequest;
use App\Models\ProductSize;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ProductSizeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductSizeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\ProductSize');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/productsize');
        $this->crud->setEntityNameStrings('product size', 'product sizes');

        $this->crud->denyAccess('create');
    }

    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID');

        $this->crud->addColumn([
            'name' => 'color_image', // The db column name
            'label' => 'Image', // Table column heading
            'type' => 'image',
            //             'prefix' => 'folder/subfolder/',
            // image from a different disk (like s3 bucket)
            // 'disk'   => 'disk-name',
            // optional width/height if 25px is not ok with you
            'height' => '50px',
            'width' => '50px',
        ]);

        CRUD::column('name');

        $this->crud->addColumn([
            // any type of relationship
            'name' => 'product', // name of relationship method in the model
            'type' => 'relationship',
            'label' => 'Product Name', // Table column heading
            // OPTIONAL
            //             'entity'    => 'skip', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            //             'model'     => App\Models\Category::class, // foreign key model
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('product', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);
        $this->crud->addColumn([
            // any type of relationship
            'name' => 'color', // name of relationship method in the model
            'type' => 'relationship',
            'label' => 'Color Name', // Table column heading
            // OPTIONAL
            //             'entity'    => 'skip', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            //             'model'     => App\Models\Category::class, // foreign key model
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('color', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);
        CRUD::column('sd_size_id')->label('SD Size ID');
        CRUD::column('mymall_id')->label('MyMall ID');
        CRUD::column('quantity');
        CRUD::column('override_price')->type('boolean');
        CRUD::column('price')->label('Price £');
        CRUD::column('price_old')->label('Price Old £');
        $this->crud->addColumn([
           'name' => 'discount_amount',
           'label' => 'Discount Amount £',
            'searchLogic' => function ($query, $column, $searchTerm) {
                    $query->where('discount_amount', 'like', '%'.$searchTerm.'%');
            }
        ]);

        $this->crud->addColumn([
            'name' => 'stock_level',
            'label' => 'Stock Level',
            'type' => 'radio',
            'options' => [
                'Green' => '<span class="bg-success p-1">Green</span>',
                'Yellow' => '<span class="bg-warning p-1">Yellow</span>',
                'Red' => '<span class="bg-danger p-1">Red</span>',
            ],
            'escaped' => false
        ]);
        CRUD::column('created_at');
        CRUD::column('updated_at');

    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(ProductSizeRequest::class);

//        CRUD::field('color_id')->attributes([
//            'disabled' => 'disabled'
//        ]);
//
//        CRUD::field('size_id')->attributes([
//            'disabled' => 'disabled'
//        ]);

        CRUD::field('sd_size_id')->attributes([
            'disabled' => 'disabled'
        ]);

        $this->crud->addField([   // relationship
            'type' => "relationship",
            'name' => 'product', // the method on your model that defines the relationship

            // OPTIONALS:
            'label' => "Product",
            'attribute' => "name", // foreign key attribute that is shown to user (identifiable attribute)
            'attributes' => [
                'disabled' => 'disabled'
            ],
            // 'entity' => 'category', // the method that defines the relationship in your Model
            // 'model' => "App\Models\Category", // foreign key Eloquent model
            // 'placeholder' => "Select a category", // placeholder for the select2 input
        ]);

        $this->crud->addField([   // relationship
            'type' => "relationship",
            'name' => 'size_id', // the method on your model that defines the relationship

            // OPTIONALS:
            'label' => "Size",
            'attribute' => "name", // foreign key attribute that is shown to user (identifiable attribute)
            // 'entity' => 'category', // the method that defines the relationship in your Model
            // 'model' => "App\Models\Category", // foreign key Eloquent model
            // 'placeholder' => "Select a category", // placeholder for the select2 input
        ]);

        CRUD::field('quantity')->type('number');
        $this->crud->addField([
            'type' => 'select_from_array',
            'name' => 'override_price',
            'options' => [
                0 => 'No',
                1 => 'Yes'
            ],
            'allows_null' => false
        ]);

        $this->crud->addField([
            'name' => 'price',
            'label' => 'Price (raw without addition)',
            'type' => 'number',
            // optionals
             'attributes' => ["step" => "any"], // allow decimals
             'prefix'     => "£",
        ]);
        $this->crud->addField([
            'name' => 'price_old',
            'label' => 'Price Old (raw without addition)',
            'type' => 'number',
            // optionals
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "£",

        ]);
        $this->crud->addField([
            'name' => 'discount_amount',
            'label' => 'Discount amount (raw without addition)',
            'type' => 'number',
            // optionals
            'attributes' => ["step" => "any"], // allow decimals
            'prefix'     => "£",

        ]);

        CRUD::field('stock_level')->type('select_from_array')
            ->options(
                ProductSize::selectRaw('DISTINCT stock_level')
                    ->pluck('stock_level', 'stock_level')
            );

        CRUD::field('mymall_id')->label('MyMall ID');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
