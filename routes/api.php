<?php

use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Login
Route::post('login','UsersController@login'); //->middleware('auth:api')
//logout
Route::get('logout','UsersController@logout')->middleware('auth:api');

//versio v1 de API en Laravel
Route::group(['prefix' => 'v1'], function() {
    //retorna totes les eines de l'usuari logejat
    Route::get('inventory','InventoryController@getInventory')->middleware('auth:api'); //
    //retorna totes les peticions que m'han fet a mi
    Route::get('petitions','PetitionsController@requestsHaveMadeMe')->middleware('auth:api');
    //retorna totes les eines amb les que es pot interactuar l'usuari
    Route::get('tools','ToolsController@iCanInteract')->middleware('auth:api');
    //retorna totes les peticions que he fet acceptades/cancelades/pendents
    Route::get('requests','RequestsController@requestIhaveMade')->middleware('auth:api');
    //resposta a una petició de eina amb id
    Route::post('petitions/{id}','PetitionsController@responseToToolRequest')->middleware('auth:api');
    //retorno info treballador amb id ( per generar trucada )
    Route::get('employees/{id}','EmployeesController@getInfo')->middleware('auth:api');
    //solicita una eina amb id
    Route::post('tools/{id}/request','ToolsController@requestAtool')->middleware('auth:api');
    //cancelem una petició pendent de contestar amb id
    Route::delete('requests/{id}','RequestsController@canelRequest')->middleware('auth:api');



});