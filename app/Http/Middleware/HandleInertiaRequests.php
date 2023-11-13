<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function share(Request $request)
    {
        if (Session()->has('language')) {
            App::setLocale(Session()->get('language'));
        } else { // This is optional as Laravel will automatically set the fallback language if there is none specified
            App::setLocale(config('app.fallback_locale'));
        }
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            /*'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },*/
            "currentLang" => app()->getLocale(),
            "defaultLang" => config('app.fallback_locale'),
            'menu' => [
                /*[
                    'key' => 'dashboard',
                    'label' => __('menu.dashboard'),
                    "iconNameCustom" => "fa-duotone fa-gauge"
                ],*/
                [
                    'key' => 'warehouse',
                    'label' => __('menu.warehouse'),
                    'iconNameCustom' => 'fa-duotone fa-warehouse',
                    "children" => array(
                        /*['routeName' => 'warehouse.makes.index', 'title' => __('menu.mmv')],*/
                       /* [
                            'label' => __('menu.mmv'),
                            'key' => 'mmv',
                            'children' => array(
                                ['key' => 'warehouse.makes.index', 'label' => __('menu.makes'), 'iconNameCustom' => 'fa-duotone fa-database',],
                                ['key' => 'warehouse.models.index', 'label' => __('menu.models'), 'iconNameCustom' => 'fa-duotone fa-car',],
                                ['key' => 'warehouse.versions.index', 'label' => __('menu.versions'), 'iconNameCustom' => 'fa-duotone fa-list',]
                            )],*/
                       /* ['key' => 'warehouse.replacements.index','label' => __('menu.replacements'),'iconNameCustom' => 'fa-duotone fa-engine'],*/
                        ['key' => 'warehouse.products.index','label' => __('menu.products'),'iconNameCustom' => 'fa-duotone fa-tag'],
                        /*['key' => 'warehouse.categories.index', 'label' => __('menu.categories'),'iconNameCustom' => 'fa-duotone fa-sitemap'],
                        ['key' => 'warehouse.sides.index', 'label' => __('menu.sides'),'iconNameCustom' => 'fa-duotone fa-arrows-left-right'],
                        [
                            'key' => 'attributes',
                            'label' => __('menu.attributes'),
                            'children' => array(
                            ['key' => 'warehouse.attributes.index','label' => __('Lista'),'iconNameCustom' => 'fa-duotone fa-list-tree'],
                            ['key' => 'warehouse.attributes.indexOption','label' => __('Opzioni'),'iconNameCustom' => 'fa-duotone fa-tag'],
                        )],*/
                    )
                ],
                /*[
                    'type' => 'divider',
                ],
                [
                    'key' => 'admin',
                    'show' => $request->user() &&  $request->user()->is_admin ? true : false,
                    'label' => __('menu.admininstration'),
                    'iconNameCustom' => 'fa-duotone fa-user-gear',
                    'children' => array(
                        ['key' => 'admininstration.users.index','label' => __('menu.users'),'iconNameCustom' => 'fa-duotone fa-user']
                    )
                ],*/
            ],
            'langs' => app('langs')

        ]);
    }
}
