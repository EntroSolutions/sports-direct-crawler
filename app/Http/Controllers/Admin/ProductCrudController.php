<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductColor;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Product');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/product');
        $this->crud->setEntityNameStrings('product', 'products');

        $this->crud->denyAccess('create');
    }

    protected function setupListOperation()
    {
        CRUD::column('id');
        $this->crud->addColumn([
            'name'      => 'image', // The db column name
            'label'     => 'Image', // Table column heading
            'type'      => 'image',
//             'prefix' => 'folder/subfolder/',
            // image from a different disk (like s3 bucket)
            // 'disk'   => 'disk-name',
            // optional width/height if 25px is not ok with you
             'height' => '50px',
             'width'  => '50px',
        ]);
        CRUD::column('name');
        $this->crud->addColumn([
            // any type of relationship
            'name'         => 'category', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Category', // Table column heading
            // OPTIONAL
            //             'entity'    => 'skip', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            //             'model'     => App\Models\Category::class, // foreign key model
        ]);
        $this->crud->addColumn([
            // any type of relationship
            'name'         => 'skipProductRule', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Skip', // Table column heading
            // OPTIONAL
            //             'entity'    => 'skip', // the method that defines the relationship in your Model
            'attribute' => 'sd_product_id', // foreign key attribute that is shown to user
            //             'model'     => App\Models\Category::class, // foreign key model
        ]);
        $this->crud->addColumn([
            // any type of relationship
            'name'         => 'brand', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Brand', // Table column heading
            // OPTIONAL
            'entity'    => 'brand', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => App\Models\Product::class, // foreign key model
        ]);
        CRUD::column('mymall_id')->label('MyMall ID');
        CRUD::column('sd_product_id')->label('SD PRODUCT ID');
        CRUD::column('created_at');
        CRUD::column('updated_at');


        // simple filter
        $this->crud->addFilter([
            'type'  => 'text',
            'name'  => 'brand_name',
            'label' => 'Brand',
        ],
        false,
        function($value) { // if the filter is active
            $this->crud->query = $this->crud->query->whereHas('brand', function ($query) use ($value) {
                $query->where('name', 'like', '%'.$value.'%');
            });
        });

        // simple filter
        $this->crud->addFilter([
            'type'  => 'text',
            'name'  => 'category_name',
            'label' => 'Category',
        ],
            false,
            function($value) { // if the filter is active
                $this->crud->query = $this->crud->query->whereHas('category', function ($query) use ($value) {
                    $query->where('name', 'like', '%'.$value.'%');
                });
            });
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(ProductRequest::class);

        $productId = Route::getCurrentRoute()->parameter('id');
        $product = Product::findOrFail($productId);

        CRUD::field('sd_product_id')
            ->label('SD Product ID')
            ->attributes([
                'disabled' => 'disabled'
            ]);

        CRUD::field('name');
        $this->crud->addField([
            'name' => 'override_name',
            'label' => 'Override name',
            'type' => 'select_from_array',
            'options' => [
                0 => 'No',
                1 => 'Yes'
            ]

        ]);

        CRUD::field('description')->type('summernote');

        // image
        $this->crud->addField([
            'label' => "Image",
            'name' => "image",
            'type' => 'color_image',
            'colorImage' => $product,
            'crop' => false, // set to true to allow cropping, false to disable
            'aspect_ratio' => 1, // omit or set to 0 to allow any aspect ratio
            // 'disk'      => 's3_bucket', // in case you need to show images from a different disk
            // 'prefix'    => 'uploads/images/profile_pictures/' // in case your db value is only the file name (no path), you can use this to prepend your path to the image src (in HTML), before it's shown to the user;
        ]);

        CRUD::field('override_product_image')->type('boolean');

        $this->crud->addField([
               // relationship
              'type' => "relationship",
              'name' => 'brand', // the method on your model that defines the relationship

              // OPTIONALS:
               'label' => "Brand",
               'attribute' => "name", // foreign key attribute that is shown to user (identifiable attribute)
               'entity' => 'brand', // the method that defines the relationship in your Model
               'attributes' => [
                   'disabled' => 'disbled'
               ],
              // 'model' => "App\Models\Category", // foreign key Eloquent model
              // 'placeholder' => "Select a category", // placeholder for the select2 input
        ]);

        CRUD::field('mymall_id')->label('MyMall ID');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
