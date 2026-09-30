<?php

namespace App\Eden\Middlewares;

use Closure;
use Config;
use Illuminate\Routing\UrlGenerator;
use App\Eden\Managements\Cache_management;

class Eden_extranet{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next){

        if(fonctionnalite('utiliser_extranet') == true && fonctionnalite('url_extranet') != "" && $_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet')){
            config(['app.asset_url' => 'https://'.fonctionnalite('url_extranet')]);
            config(['app.url' => 'https://'.fonctionnalite('url_extranet')]);

            $app = app();

            $routes = $app['router']->getRoutes();

            $app->instance('url', new UrlGenerator(
                $routes, $app->rebinding(
                    'request', function ($app, $request) {
                        $app['url']->setRequest($request);
                    }
                ), $app['config']['app.asset_url']
            ));
        }
 
        return $next($request);
    }
}