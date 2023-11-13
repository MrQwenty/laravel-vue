<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switchLang($lang)
    {
        foreach(app('langs') as $lan){

            if($lan['value'] == $lang){
                App::setLocale($lang);
                Session::put('language', $lang);
            }
        }

        return redirect()->back();
    }
}
