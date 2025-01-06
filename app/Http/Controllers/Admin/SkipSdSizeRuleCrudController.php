<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SkipSdSizeRuleRequest;
use App\Models\Size;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SkipSdSizeRuleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SkipSdSizeRuleCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\SkipSdSizeRule');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/skipsdsizerule');
        $this->crud->setEntityNameStrings('skip size rule', 'skip size rules');
    }

    protected function setupListOperation()
    {
        CRUD::column('id');

        $this->crud->addColumn([
            'name'      => 'size',
            'type'      => 'relationship',
            'label'     => 'Size',
            'attribute' => 'name',
        ]);

        $this->crud->addColumn([
            'name'      => 'size',
            'key'       => '1',
            'type'      => 'relationship',
            'label'     => 'Category name',
            'attribute' => 'category_name',
        ]);

        CRUD::column('skip')->type('boolean');
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(SkipSdSizeRuleRequest::class);

        $sizes = Size::select('name', 'id', 'category_name')
            ->get()
            ->map(function($item) {
                $item->name = $item->name . ' (' . $item->category_name . ')';
                return $item;
            })
            ->pluck('name','id');

        $this->crud->addField([
            'name' => 'size_id',
            'type' => 'select2_from_array',
            'options' => $sizes
        ]);

        CRUD::field('skip')
            ->type('select2_from_array')
        ->options([
            1 => 'Yes',
            0 => "No"
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

}
