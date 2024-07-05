<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class Winmax4DatatablesController extends Controller
{
    public function entitiesDatatable(Request $request)
    {
        $entities = (new Winmax4Controller)->getEntities();

        return DataTables::of($entities)
            ->editColumn('name_style', function($entity){
                return $entity->name;
            })
            ->editColumn('type_style', function($entity){
                return $entity->entity_type == 0 ? 'Client' : 'Supplier';
            })
            ->editColumn('nif_style', function($entity){
                return $entity->tax_payer_id;
            })
            ->editColumn('newsletter_style', function($entity){
                return $entity->newsletter ? 'Yes' : 'No';
            })
            ->addColumn('actions', function($entity){
                return '<button class="btn btn-primary btn-sm" onclick="editEntity('.$entity->ID.')">Edit</button>';
            })
            ->rawColumns(['name_style', 'type_style', 'nif_style', 'newsletter_style', 'actions'])
            ->make(true);
    }
}
