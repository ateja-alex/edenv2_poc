<?php

namespace App\Eden\Middlewares;

use Closure;

class Id_utilisateur_api
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        if(!defined('id_utilisateur'))
		    define('id_utilisateur', 0);
		
        return $next($request);
    }
}
