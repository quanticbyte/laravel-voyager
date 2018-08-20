<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\Http\Controllers\Controller;
//use Laravel\Passport\HasApiTokens;
/*
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
*/

class UsersController extends Controller
{
    //login

    public function login( Request $request )
    {
        $bodyContent = $request->getContent();
        $bodyContent = json_decode($bodyContent,true);

		$user = DB::table('users')->where('email' , $bodyContent['email'])->first();

		if (Auth::attempt(array('email' => $bodyContent['email'], 'password' => $bodyContent['password']), true))
		{

			$remember_token = DB::table('users')->where('id',$user->id)->select('remember_token')->first();
			//actualitzem api_token
			DB::table('users')->where('id',$user->id)->update( array('api_token' => $remember_token->remember_token) );

		            return response()->json([
		              'status'  => Response::HTTP_OK,
		              'token' => $remember_token->remember_token,
		            ]);
		}
		else
		{
			            return response()->json([
			                'status'    => Response::HTTP_UNPROCESSABLE_ENTITY,
			                'message'   => "Error en registre.",
			            ]);
		}

    }

    //logouth

    public function logout()
    {
        if(Auth::check())
        {
    		$user_id = Auth::id();
    		$update = ['api_token' => '', 'remember_token' => ''];
    		DB::table('users')->where('id',$user_id)->update( $update );

            return response()->json([
              'status'  => Response::HTTP_OK,
              'message' => 'Bye!',
            ]);

    	}
    	else
    	{
			            return response()->json([
			                'status'    => Response::HTTP_UNPROCESSABLE_ENTITY,
			                'message'   => "Error en registre.",
			            ]);    		
    	}

    }

}
