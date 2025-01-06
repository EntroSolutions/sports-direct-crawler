<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CategoryRequest;
use App\Jobs\ProcessCategory;
use App\Models\Category;
use App\MyMall\Traits\BackpackAlert;
use App\MyMall\Traits\CsCartApi;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

/**
 * Class CategoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use BackpackAlert;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use CsCartApi;

    public function setup()
    {
        $this->crud->setModel('App\Models\Category');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/category');
        $this->crud->setEntityNameStrings('category', 'categories');

        $this->crud->denyAccess('create');
    }

    protected function setupListOperation()
    {

        $this->crud->addButtonFromModelFunction('line', 'Crawl Now', 'crawlNowButton', 'beginning');

        CRUD::column('id');
        CRUD::column('name');
        CRUD::column('sd_category_id')->label('SD Category ID');

        $this->crud->addColumn([   // 1-n relationship
            'label'       => "Mymall Category", // Table column heading
            'type'        => "select_from_array",
            'name'        => 'mymall_id', // the column that contains the ID of that connected entity
            'options'     => $this->getCsCartCategories()
        ]);

        CRUD::column('url')->limit(200);
        CRUD::column('sync_started_at')->label('Sync started at');
        CRUD::column('sync_ended_at')->label('Sync ended at');
        CRUD::column('sync_message')->limit(400)->escaped(false);
        $this->crud->addColumn([
            // any type of relationship
            'name'         => 'skipCategoryRule', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Skip', // Table column heading
            // OPTIONAL
//             'entity'    => 'skip', // the method that defines the relationship in your Model
             'attribute' => 'skip', // foreign key attribute that is shown to user
//             'model'     => App\Models\Category::class, // foreign key model
        ]);
        CRUD::column('created_at');
        CRUD::column('updated_at');
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(CategoryRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    public function update()
    {
        $response = $this->traitUpdate();

        $this->crud->entry->skipCategoryRule
            ->update([
                'skip' => request('skip')
            ]);

        return $response;
    }

    protected function setupUpdateOperation()
    {
        $this->crud->setValidation(CategoryRequest::class);

        $categoryId = Route::getCurrentRoute()->parameter('id');
        $category = Category::findOrFail($categoryId);

        CRUD::field('name');

        CRUD::field('sd_category_id')->label('SD Category ID');

        $this->crud->addField([   // 1-n relationship
            'label'       => "Mymall Category", // Table column heading
            'type'        => "select2_from_array",
            'name'        => 'mymall_id', // the column that contains the ID of that connected entity
            'options'     => $this->getCsCartCategories()
        ]);


        $this->crud->addField([   // repeatable
            'name'  => 'subcategories',
            'label' => 'Mymall additional categories',
            'type'  => 'repeatable',
            'fields' => [
                [   // 1-n relationship
                    'label'       => "Mymall Category", // Table column heading
                    'type'        => "select2_from_array",
                    'name'        => 'mymall_id', // the column that contains the ID of that connected entity
                    'options'     => $this->getCsCartCategories()
                ]
            ],

            // optional
            'new_item_label'  => 'Add Group', // customize the text of the button
            'init_rows' => 0, // number of empty rows to be initialized, by default 1
            'min_rows' => 0, // minimum rows allowed, when reached the "delete" buttons will be hidden
            'max_rows' => 100, // maximum rows allowed, when reached the "new item" button will be hidden
            'wrapper'  => [
                'class' => 'form-group col-sm-12 border border-info rounded mx-3 p-4 col-md-11'
            ]

        ]);


        CRUD::field('url')->limit(200);
        CRUD::field('sync_started_at')->label('Sync started at');
        CRUD::field('sync_ended_at')->label('Sync ended at');
        CRUD::field('sync_message')->attributes(['disabled' => 'disabled'])->escaped(false);
        $this->crud->addField([   // CustomHTML
            'name'  => 'skip',
            'type'  => 'custom_html',
            'value' => '<label>Skip</label><select class="form-control" name="skip"><option ' . ($category->skipCategoryRule->skip == 0 ? "selected" : "") . ' value="0">No</option><option ' . ($category->skipCategoryRule->skip == 1 ? "selected" : "") . ' value="1">Yes</option></select>'
        ]);
        CRUD::field('created_at');
        CRUD::field('updated_at');
    }

    public function crawlNow(int $categoryId)
    {
        $category = Category::find($categoryId);

        ProcessCategory::dispatch($category)
            ->onQueue(config('queue.categories_queue'));

        $this->alert($category->name . ' ' . $category->sd_category_id, 'start processing...', 'info', true);

        return redirect()->back();
    }

    public function getCsCartCategories()
    {
        $url = config('cscart.api_base') . 'categories&items_per_page=50000';

        $csCategoriesResponse = json_decode($this->getRequest($url));

        if(!isset($csCategoriesResponse->categories)){
            $this->alert('You have no mapped categories or they cannot be taken from the API', 'You should map all of them or contact a developer','danger');
            return [];
        }

        $csCategories = $csCategoriesResponse->categories;

        if(!$csCategories){
            $this->alert('Not categories found for mapping', 'You should map all of them','danger');
            return [];
        }

        return Arr::pluck(
            $csCategories, 'category', 'category_id'
        );
    }
}
