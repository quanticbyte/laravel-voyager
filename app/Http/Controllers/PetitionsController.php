<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class PetitionsController extends Controller
{
    public function requestsHaveMadeMe()
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

        //mirem totes les peticions que m'han fet ... totes les pendents de respondre
        $peticions = DB::table('tool_request')->where([
                ['requested_id', '=', $employee_id],
                ['company_id', '=', $company_id],
                ['state', '=', 0],
            ])->get();
        
        return response()->json([
                'status'    => Response::HTTP_OK,
                'token'     => $user_token,
                'data'      => $peticions,
            ]);
    	}

    }


    //Resposta a una peticio de eina
/*
    0 - pendent
    1 - acceptada
    2 - denegada
    3 - anulada

    POST /petitions/id/
    rebut:
    request[
    response:acepted/rejected
    ]

    responem:
    status:true/false

*/
    //------------------------------------------------------------------------------------------------
    public function responseToToolRequest(Request $request, $id)
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

            $id_peticion = $id;

            //return $request;
            $bodyContent = $request->getContent();
            
            $bodyContent = json_decode($bodyContent,true);
            
            //$response = $bodyContent['response'];
            //{"respone":"accepted"}
            if(isset($bodyContent['response']))
            {
                if($bodyContent['response']=='accepted')
                {
                    //return "acceptem";
                    //aqui modifiquem el estat de la peticio a acceptat i enviem missatge
                    //return "acceptem :".$id_peticion;
                    //comprobem que som qui ha de rebre la peticio
                    $peticio = DB::table('tool_request')->where('id',$id_peticion)->get();
                    $peticio = $peticio[0];
                    $requester_id = $peticio->requester_id;
                    $tool_id = $peticio->tool_id;
                    if($peticio->requested_id == $employee_id)
                    {
                        //Ok podem canviar estat
                        //$query = "UPDATE `tool_request` SET `updated_at`=now(),`accepted_at`=now(),`state`=1 WHERE `id`=$id_peticio AND `requested_id`= $employee_id";
                         DB::table('tool_request')->where('id',$id_peticion)->update(['updated_at' => now(), 'state' => 1]);
                         //return " actualitzat id : ".$id_peticion;
                        /*
                        $request = return_tool_request_by_id($id_peticio);
                        $employeeUser = retorna_user_data_by_employee($request['requester_id']);
                        send_alert($employeeUser['push_token'], 'Solicitud confirmada', 'Solicitud de herramienta confirmada');
                        */
                        //modificant user que te l'eina
                        DB::table('tools')->where('id',$tool_id)->update(['employee_id' => $requester_id]);
                        //$query = "UPDATE `tools` SET `employee_id` = $requester WHERE `id` = $tool_id ";
                        return response()->json([
                            'status'    => Response::HTTP_OK,
                            'token'     => $user_token,
                        ]);
                    } 
                }

                elseif($bodyContent['response']=='rejected')
                {
                    //return "cancelem";
                    //aqui modifiquem estat petició a negat i enviem missatge
                    //            $query = "UPDATE `tool_request` SET `updated_at`=now(),`accepted_at`=now(),`state`=2 WHERE `id`=$id_peticio AND `requested_id`= $employee_id";
                    DB::table('tool_request')->where('id',$id_peticion)->update(['updated_at' => now(), 'state' => 2]);
                    /*
                        $request = return_tool_request_by_id($id_peticio);
                        $employeeUser = retorna_user_data_by_employee($request['requester_id']);
                        send_alert($employeeUser['push_token'], 'Solicitud rechazada', 'Solicitud de herramienta rechazada');
                    */
                    return response()->json([
                        'status'    => Response::HTTP_OK,
                        'token'     => $user_token,
                    ]);
                }

                else
                {
                    //return "error";
                    //aqui no tindriem que arrivarhi
                    //$resposta = array("status"=>false,"message"=>"Error en parametre response.");
                    return response()->json([
                        'status'    => Response::HTTP_NOT_FOUND,
                        'token'     => $user_token,
                        'message'   => "Error en parametre response.",
                    ]);
                }
            }

        }
    }
}
