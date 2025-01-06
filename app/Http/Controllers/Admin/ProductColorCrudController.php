<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductColorRequest;
use App\Models\ColorImage;
use App\Models\ProductColor;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class ProductColorCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductColorCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }

    public function setup()
    {
        $this->crud->setModel('App\Models\ProductColor');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/productcolor');
        $this->crud->setEntityNameStrings('product color', 'product colors');

        $this->crud->denyAccess('create');
    }

    protected function setupListOperation()
    {
        CRUD::column('id');

        $this->crud->addColumn([
            'name' => 'product_image_url', // The db column name
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
//            'entity'    => 'brand', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
//            'model'     => App\Models\Product::class, // foreign key model
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('product', function ($q) use ($column, $searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            }
        ]);
        CRUD::column('sd_color_id')->label('SD Color ID');
        CRUD::column('mymall_id')->label('MyMall ID');
        CRUD::column('created_at');
        CRUD::column('updated_at');
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(ProductColorRequest::class);

        CRUD::field('sd_color_id')
            ->label('SD Color ID')
            ->attributes([
                'disabled' => 'disabled'
            ]);

        $this->crud->addField([
            // relationship
            'type' => "relationship",
            'name' => 'color', // the method on your model that defines the relationship

            // OPTIONALS:
            'label' => "Color name",
            'attribute' => "name", // foreign key attribute that is shown to user (identifiable attribute)
            'entity' => 'color', // the method that defines the relationship in your Model
            'attributes' => [
                'disabled' => 'disabled'
            ]
            // 'model' => "App\Models\Category", // foreign key Eloquent model
            // 'placeholder' => "Select a category", // placeholder for the select2 input
        ]);

        $productColorId = Route::getCurrentRoute()->parameter('id');
        $productColor = ProductColor::findOrFail($productColorId);

        foreach ($productColor->colorImages as $k => $colorImage) {

            $this->crud->addField([
                'type' => 'color_image',
                'name' => "image[" . $colorImage->id . "]",
                'label' => 'Image ' . $k + 1,
                'colorImage' => $colorImage,
            ]);

            $this->crud->addField([
                'name' => 'override_image[' . $colorImage->id . ']',
                'type' => 'boolean',
                'label' => 'Override image ID ' . $colorImage->id,
                'value' => $colorImage->override
            ]);

            CRUD::field('separator' . $colorImage->id)->type('custom_html')->value('<hr>');
        }

        CRUD::field('mymall_id')->label('MyMall ID');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function update()
    {
        $response = $this->traitUpdate();

        // store color images
        foreach (request()->image as $colorImageId => $image) {

            $colorImage = ColorImage::findOrFail($colorImageId);
            $imageUrl = $image;

            if(Str::contains($image, 'data:image')){
                $imageUrl = $colorImage->storeImage($image, $colorImageId);
            }

            $colorImage->update([
                    'image' => $imageUrl,
                    'override' => request('override_image')[$colorImageId]
                ]);

        }

        return $response;
    }
}
