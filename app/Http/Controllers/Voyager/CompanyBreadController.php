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
            
            $resposta_admin = DB::insert('insert into users (role_id, name, email, password, avatar, created_at ) values (?, ?, ?, ?, ?, ?)', [
                6, //Admin empresa
                $send_data['admin_name'],
                $send_data['admin_mail'],
                Hash::make($send_data['admin_psw']),
                'users/admin_company.png',
                now()
            ]);
            
            //creant usuari per Magatzem de la empresa

            $resposta_magatzem = DB::insert('insert into users (role_id, name, email, password, avatar, created_at ) values (?, ?, ?, ?, ?, ?)', [
                5, //Magatzem empresa
                $send_data['magatzem_name'],
                $send_data['magatzem_mail'],
                Hash::make($send_data['magatzem_psw']),
                'users/almacen.png',
                now()
            ]);
            
            //agregant nou treballador Magatzem a la empresa
            //tragem el id de empresa a traves del seu CIF

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






}
