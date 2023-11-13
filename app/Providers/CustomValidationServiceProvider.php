<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Facades\Translatable;

class CustomValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        Validator::extend('exists_trans',function ($attribute,$value,$parameters,$validator){
            $primaryLang = false;
            foreach ($value as $key => $item){
                if($key == app()->getLocale()){
                    $primaryLang = true;
                    if(empty($item)){
                        return false;
                    }
                }
            }
            if(!$primaryLang) return false;
            return true;
        });

        Validator::replacer('exists_trans', function ($message, $attribute, $rule, $parameters) {
            return __('validation.transError');
        });
    }
}
