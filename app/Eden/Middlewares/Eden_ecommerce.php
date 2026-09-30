<?php

namespace App\Eden\Middlewares;

use Closure;

class Eden_ecommerce
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
        
        if(!defined('eden_page_ecommerce'))
		    define('eden_page_ecommerce', true);
		
        return $next($request);
    }
}
