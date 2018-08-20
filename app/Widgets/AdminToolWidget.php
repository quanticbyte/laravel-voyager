<?php

namespace App\Widgets;

use Arrilot\Widgets\AbstractWidget;
use TCG\Voyager\Facades\Voyager;
use App\Tool;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class AdminToolWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //-------------------------------------------------------------------------------------------------------------------
        // Filtramos por usuario al que se le està mostrando el widget
        $user_id = Auth::id();
        // miramos a que empresa pertenece
        // como es admin i no tiene employee lo miramos en la tabla users_company
        $company_id = DB::table('users_company')->where('user_id', $user_id)->first();
        $company_id = $company_id->company_id;

        //Contamos los trabajadores de la empresa
        $num_employees = DB::table('tools')->where('company_id', $company_id)->count();
        //miramos nombre empresa
        $company_name = DB::table('companies')->where('id', $company_id)->value('comercial_name');

        //-------------------------------------------------------------------------------------------------------------------

        $count = $num_employees;
        $string = 'Herramientas';
        $text_llarg = ':company_name tiene :count :string . Haga clic en el botón de abajo para ver todas las :string. ';
        return view('widgets.admin_tool_widget', array_merge($this->config, [
            'icon'   => 'voyager-tools',
            'title'  => "{$count} {$string}",
            'text'   => __($text_llarg, ['count' => $count, 'string' => Str::lower($string), 'company_name' => $company_name]),
            'button' => [
                #'text' => __('voyager::dimmer.user_link_text'),
                'text' => 'Ver todas las herramientas',
                'link' => route('voyager.tools.index'),
            ],
            'image' => '/tool-bg.jpg',
        ]));
    }

    public function shouldBeDisplayed()
    {
        return Auth::user()->hasPermission('add_tools');
    }

}