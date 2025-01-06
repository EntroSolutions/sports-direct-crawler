<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TranslateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TranslateCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TranslateCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Translate');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/translate');
        $this->crud->setEntityNameStrings('translate', 'translates');
        $this->crud->enableExportButtons();
    }

    protected function setupListOperation()
    {
        CRUD::column('en');
        CRUD::column('bg');
        CRUD::column('el');
        CRUD::column('ro');

        // simple filter
        $this->crud->addFilter([
            'type' => 'simple',
            'name' => 'missing_translations_en',
            'label' => 'Missing translations EN'
        ],
            false,
            function () { // if the filter is active
                $this->crud->addClause('where', 'en', '=', '');
                $this->crud->addClause('orWhereNull', 'en');
            });

        // simple filter
        $this->crud->addFilter([
            'type' => 'simple',
            'name' => 'missing_translations_bg',
            'label' => 'Missing translations BG'
        ],
            false,
            function () { // if the filter is active
                $this->crud->addClause('where', 'bg', '=', '');
                $this->crud->addClause('orWhereNull', 'bg');
            });

        // simple filter
        $this->crud->addFilter([
            'type' => 'simple',
            'name' => 'missing_translations_el',
            'label' => 'Missing translations EL'
        ],
            false,
            function () { // if the filter is active
                $this->crud->addClause('where', 'el', '=', '');
                $this->crud->addClause('orWhereNull', 'el');
            });

        // simple filter
        $this->crud->addFilter([
            'type' => 'simple',
            'name' => 'missing_translations_ro',
            'label' => 'Missing translations RO'
        ],
            false,
            function () { // if the filter is active
                $this->crud->addClause('where', 'ro', '=', '');
                $this->crud->addClause('orWhereNull', 'ro');
            });
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(TranslateRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        CRUD::field('en');
        CRUD::field('bg');
        CRUD::field('el');
        CRUD::field('ro');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
