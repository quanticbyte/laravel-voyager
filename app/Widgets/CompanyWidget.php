<?php

namespace App\Widgets;

use Arrilot\Widgets\AbstractWidget;
use TCG\Voyager\Facades\Voyager;
use App\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyWidget extends AbstractWidget
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
        #$count = Voyager::model('Company')->count();
        $count = Company::count();
        #$string = trans_choice('voyager::dimmer.user', $count);
        $string = 'Empresas';
        $text_llarg = 'Tiene :count :string en su base de datos. Haga clic en el botón de abajo para ver todas las empresas. ';
        return view('widgets.company_widget', array_merge($this->config, [
            'icon'   => 'voyager-company',
            'title'  => "{$count} {$string}",
           # 'text'   => __('voyager::dimmer.user_text', ['count' => $count, 'string' => $string]),
           # 'text'   => __('voyager::dimmer.user_text', ['count' => $count, 'string' => Str::lower($string)]),
           # 'text'   => __('voyager::dimmer.user_text', ['count' => $count, 'string' => Str::lower($string)]),
            'text'   => __($text_llarg, ['count' => $count, 'string' => Str::lower($string)]),
            'button' => [
                #'text' => __('voyager::dimmer.user_link_text'),
                'text' => 'Ver todas las Empresas',
                //'link' => route('voyager.users.index'),
                'link' => route('voyager.companies.index'),
            ],
            #'image' => voyager_asset('images/widget-backgrounds/01.jpg'),
            'image' => '/company-bg.jpg',
        ]));
    }

    public function shouldBeDisplayed()
    {
        return Auth::user()->can('browse', Voyager::model('Page'));
    }


}
