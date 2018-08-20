<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Events\BreadImagesDeleted;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;

use TCG\Voyager\Http\Controllers\VoyagerBaseController;

//afegits
use Illuminate\Support\Facades\Hash; //tema generacio passw
//use App\Company;

class CompanyBreadController extends VoyagerBaseController
{

    /**
     * POST BRE(A)D - Store data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */

    /*
        Creem la empresa i al mateix temps creem el usuari Admin per aquesta, el usuari Magatzem per aquesta i el employee
        Magatzem per aquesta.
    */
        
    public function store(Request $request)
    {
        
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->has('_validate')) {

            $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

            //---------------------------------------------------------------------
            //Ok aki s'ha creat la empresa correctament
            //creem el usuari Admin per l'empresa

            $send_data = $val->getData();
            //dd($send_data['admin_name']);
            
            //crean usuari Admin per l'empresa amb nom $send_data['admin_name']
            
            $resposta_admin = DB::insert('insert into users (role_id, name, email, password, avatar, created_at, api_token ) values (?, ?, ?, ?, ?, ?, ?)', [
                6, //Admin empresa
                $send_data['admin_name'],
                $send_data['admin_mail'],
                Hash::make($send_data['admin_psw']),
                'users/admin_company.png',
                now(),
                Hash::make($send_data['admin_psw'])
            ]);
            
            //creant usuari per Magatzem de la empresa

            $resposta_magatzem = DB::insert('insert into users (role_id, name, email, password, avatar, created_at, api_token ) values (?, ?, ?, ?, ?, ?, ?)', [
                5, //Magatzem empresa
                $send_data['magatzem_name'],
                $send_data['magatzem_mail'],
                Hash::make($send_data['magatzem_psw']),
                'users/almacen.png',
                now(),
                Hash::make($send_data['magatzem_psw'])
            ]);
            
            //agregant nou treballador Magatzem a la empresa
            //tragem el id de empresa a traves del seu CIF

/*  ***************************************************************************************************
    
    seria molt mes practic fer servir el insertGetId()
    https://laravel.com/docs/5.6/queries

*************************************************************************************************** */
            

            $id_company = DB::select('select id from companies where cif = :cif', ['cif' => $send_data['cif']]);
            $id_company = $id_company[0]->id;

            //tragent el id del usuari magatzem que hem creat

            $id_magatzem_user = DB::table('users')->where('role_id',5)
            ->where('name',$send_data['magatzem_name'])
            ->where('email',$send_data['magatzem_mail'])
            ->select('id')->get();
            $id_magatzem_user = $id_magatzem_user[0]->id;

            //creant el treballador Magatzem

            $resposta_magatzem_employee = DB::insert('insert into employees (company_id, user_id, id_op_empresa, firstname, lastname, phone, created_at ) values (?, ?, ?, ?, ?, ?, ?)', [
                $id_company,
                $id_magatzem_user,
                'Almacén',
                $send_data['magatzem_name'],
                '',
                $send_data['magatzem_tlf'],
                now()
            ]);
                     
            //afegint relació a la taula users_company
            //relació admin empresa
            //buscant el id de usuari
            $admin_user_id = DB::table('users')->where('role_id',6)
            ->where('name' , $send_data['admin_name'])
            ->where('email' , $send_data['admin_mail'])
            ->select('id')->get();
            $admin_user_id = $admin_user_id[0]->id;
            //afegint relació a la taula
            DB::insert('insert into users_company (user_id, company_id, created_at) values (?, ?, ?)', [$admin_user_id, $id_company, now()]); 

            //relació user magatzem
            //afegint relació a la taula
            DB::insert('insert into users_company (user_id, company_id, created_at) values (?, ?, ?)', [$id_magatzem_user, $id_company, now()]); 


            //---------------------------------------------------------------------

            event(new BreadDataAdded($dataType, $data));

            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $data]);
            }

            return redirect()
                ->route("voyager.{$dataType->slug}.index")
                ->with([
                        'message'    => __('voyager::generic.successfully_added_new')." {$dataType->display_name_singular}",
                        'alert-type' => 'success',
                    ]);

        }
    }



//----------------------------------------------------------------------------------
/*
    public function create(Request $request)
    {
        $slug = $this->getSlug($request);

        // es crea el dataType
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        $dataTypeContent = (strlen($dataType->model_name) != 0)
                            ? new $dataType->model_name()
                            : false;

        foreach ($dataType->addRows as $key => $row) {
            $details = json_decode($row->details);
            $dataType->addRows[$key]['col_width'] = isset($details->width) ? $details->width : 100;
        }

        //dd($dataType);
        //------------------------------------------------------------------------------------------
        //  mirem de afegir mes dades a les relacións que es passen al formulari
        // añadir columnas al $dataType


        //------------------------------------------------------------------------------------------


        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'add');

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }

        //-----------------------------------------------------------------------------------------

        $user_id = Auth::id();
        $user_rol = Auth::user()->role;
        $user_rol = $user_rol->getAttributes();
        $user_rol = $user_rol['name']; // user, admin, company, employee, magatzem, company admin

        $array_rols_use = ['magatzem','company admin'];

        //comprobem si esta en el array
        if( in_array($user_rol, $array_rols_use) )
        {

            $view = "voyager::bread.edit-add-employee-user";

        }

        //-----------------------------------------------------------------------------------------

        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }
*/
//----------------------------------------------------------------------------------







}
