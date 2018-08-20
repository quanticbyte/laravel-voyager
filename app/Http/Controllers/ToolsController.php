<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ToolsController extends Controller
{
    //iCanInteract Retorna totes les eines amb les que l'usuari pot interactuar
	public function iCanInteract()
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

        //mirem totes les eines de la empresa que tenen state = 1 ( són transferibles ) i is_active = 1
        //i employe_id != mi_id de employee
        $tools = DB::table('tools')->where([
                ['company_id', '=', $company_id],
                ['state', '=', 1],
                ['is_active', '=', 1],
                //['employee_id', '!=', $employee_id],
            ])->get();

        //retornem llistat eines
        return response()->json([
                'status'    => Response::HTTP_OK,
                'token'     => $user_token,
                'data'      => $tools,
            ]);
		}
	}

    //solicitar una eina
    public function requestAtool(Request $request, $id)
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
        $tool_id = $id;

        //comprobem que la eina es de la empresa o del grup d'empresas
        $tool = DB::table('tools')->where('id',$tool_id)->get();
        if (!isset($tool[0]))
        {
            //return "no existeix l'eina";
            return response()->json([
                'status'    => Response::HTTP_UNPROCESSABLE_ENTITY,
                'token'     => $user_token,
                'message'   => "Error eina inexistent.",
            ]);
        }
        elseif ($tool[0]->company_id == $company_id)
        {
            //return "solicitant eina";
            //         $query = "INSERT INTO `tool_request`(`requester_id`, `requested_id`, `tool_id`, `company_id`,`state`) VALUES ($employee_id, $id_operari_te_eina,$id_peticio,$empresa_id,0)";
            //DB::table('users')->insert(['email' => 'john@example.com', 'votes' => 0]);
            $id = DB::table('tool_request')->insertGetId([
                'requester_id'  => $employee_id,
                'requested_id'  => $tool[0]->employee_id,
                'tool_id'       => $tool_id,
                'company_id'    => $company_id,
                'state'         => 0,
                'created_at'     => now(),
            ]);

            //aqui ENVIEM MISSATGE
            //generem resposta
            $resp = array(
                            "id" => $id, 
                            "state" => 'pending' ,
                            "requested_id" => $tool[0]->employee_id,
                            'tool' => $tool[0],
                        );
            
            //emviem info
            return response()->json([
                    'status'    => Response::HTTP_OK,
                    'token'     => $user_token,
                    'data'      => $resp,
                ]);


        }
        else
        {
            //return "no existeix eina dins de l'empresa";
            return response()->json([
                'status'    => Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS,
                'token'     => $user_token,
                'message'   => "Error no existeix eina dins d'empresa.",
            ]);
        }

        return $tool;

    }
}
