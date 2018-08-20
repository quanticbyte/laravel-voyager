<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class RequestsController extends Controller
{
    //totes les peticions que he fet acceptades/pendents i cancelades
    //------------------------------------------------------------------------------------------------
    public function requestIhaveMade()
    {
        if(Auth::check())
        {
        //recuperamos user identificado
        $user_id = Auth::id();
        //recuperamos employee_id & companie_id
        $user_data  = DB::table('employees')->where('user_id',$user_id)->select('id','company_id')->get();
        $user_data = json_decode($user_data, true)[0];
            $employee_id = $user_data['id'];
            $company_id  = $user_data['company_id'];
        //agafem el api_token de la petició
        $user_token = Auth::user()->api_token;

        //mirem totes les peticions que he fet
        $peticions = DB::table('tool_request')->where([
                ['requester_id', '=', $employee_id],
                ['company_id', '=', $company_id],
            ])->get();

        return response()->json([
                'status'    => Response::HTTP_OK,
                'token'     => $user_token,
                'data'      => $peticions,
            ]);
        }        
    }

    //Cancelem peticio amb id
    public function canelRequest(Request $request, $id)
    {
        //recuperamos user identificado
        $user_id = Auth::id();
        //recuperamos employee_id & companie_id
        $user_data  = DB::table('employees')->where('user_id',$user_id)->select('id','company_id')->get();
        $user_data = json_decode($user_data, true)[0];
            $employee_id = $user_data['id'];
            $company_id  = $user_data['company_id'];
        //agafem el api_token de la petició
        $user_token = Auth::user()->api_token;

        $id_peticio = $id;

        //mirem l'estat de la peticio i si es 0 i es nostra dons podem cancelar (state=3)
        //agafem peticio
        $peticio = DB::table('tool_request')->where('id',$id_peticio)->get();

        //return $peticio;
        if(!isset($peticio[0]))
        {
            return response()->json([
                'status'    => Response::HTTP_UNPROCESSABLE_ENTITY,
                'token'     => $user_token,
                'message'   => "Error petició inexistent.",
            ]);
        }
        elseif($peticio[0]->requester_id == $employee_id && $peticio[0]->state == 0)
        {
            //borrem peticio cambiem estat a 3
            DB::table('tool_request')->where('id',$id_peticio)->update(['updated_at' => now(), 'state' => 3]);
            
            return response()->json([
                'status'    => Response::HTTP_OK,
                'token'     => $user_token,
                'message'   => "Petició eliminada.",
            ]);            

        }
        else
        {
            //error al borrar la peticio
            return response()->json([
                'status'    => Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS,
                'token'     => $user_token,
                'message'   => "Error no tens permis per eliminar aquesta petició.",
            ]);
        }

    }
}
