<?php

namespace App\Widgets;

use Arrilot\Widgets\AbstractWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DemoWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    public $reloadTimeout = 30; //temps de refresc per acualitzar dades

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {

        //-------------------------------------------------------------------------------------------------
        // Dades de l'empresa
        $user_id = Auth::id();
        $company_id = DB::table('users_company')->where('user_id', $user_id)->first();
        $company_id = $company_id->company_id;

        //miramos nombre empresa
        $company_name = DB::table('companies')->where('id', $company_id)->value('comercial_name');
        //-------------------------------------------------------------------------------------------------
        // numero peticións per contestar
        $pet_per_contestar = DB::table('tool_request')
            ->where('company_id', $company_id)
            ->where('state', 0)
            ->count();
        //-------------------------------------------------------------------------------------------------
        // numero peticións contestades
        $pet_contestades = DB::table('tool_request')
            ->where('company_id', $company_id)
            ->where('state',1)
            ->orWhere('state',2)
            ->count();

        $pet_acceptades = DB::table('tool_request')
            ->where('company_id', $company_id)
            ->where('state',1)
            ->count();
        $pet_denegades  = DB::table('tool_request')
            ->where('company_id', $company_id)
            ->where('state',2)
            ->count();
        //-------------------------------------------------------------------------------------------------


        return view('widgets.demo_widget', array_merge($this->config, [
            'icon'   => 'voyager-pie-chart',
            'title'  => $company_name,
            'text'   => 'Peticiones',
            'image' => '/mi-widget.jpg',
            'per_contestar' => $pet_per_contestar,
            'contestades'   => $pet_contestades,
            'acceptades'    => $pet_acceptades,
            'denegades'     => $pet_denegades,
        ]));

    }

    public function shouldBeDisplayed()
    {
        return Auth::user()->hasPermission('add_employees');
    }

}
