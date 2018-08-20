<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class EmployeesController extends Controller
{
	//retorna la info de un treballador amb id per generar trucada
	public function getInfo(Request $request, $id)
    {
        if(Auth::check())
        {
            //recuperamos user identificado
            $user_id = Auth::id();
            //recuperamos employee_id & companie_id
            $user_data  = DB::table('employees')->where('user_id',$user_id)->select('id','company_id')->get();
            
            //return $user_data;

            $user_data = json_decode($user_data, true)[0];
                $employee_id = $user_data['id'];
                $company_id  = $user_data['company_id'];
            //agafem el api_token de la petició
            $user_token = Auth::user()->api_token;

            //comprobem que són de la mateixa empresa ( o grup d'empresas )
            $solicitat_id = $id;
            //$consulta = DB::table('employees')->where('user_id',$user_id)
            $dades = DB::table('employees')->where('user_id',$solicitat_id)->get();

            //return $dades;
            if (!isset($dades[0])) 
            { 
	            return response()->json([
	                'status'    => Response::HTTP_UNPROCESSABLE_ENTITY,
	                'token'     => $user_token,
	                'message'   => "Error Operari inexistent.",
	            ]);
            }
            
            elseif($company_id == $dades[0]->company_id)
            {
            	//$data = $employee[$dades];
            	//$employee["employee":$dades];
            	$employee = array("employee"=>$dades);
	            return response()->json([
	                'status'    => Response::HTTP_OK,
	                'token'     => $user_token,
	                'data'      => $employee,
	            ]);

            }
            else
            {
	            return response()->json([
	                'status'    => Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS,
	                'token'     => $user_token,
	                'message'   => "Error Operaris de diferentes empreses.",
	            ]);
            }

            

        }

    }
}
