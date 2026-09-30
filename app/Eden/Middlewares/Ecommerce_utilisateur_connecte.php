<?php

namespace App\Eden\Middlewares;

/*
use App\Eden\Models\Elements\Utilisateur;

use Illuminate\Support\Facades\Cookie; 
*/

use Closure;
use App;
use Cookie;

class Ecommerce_utilisateur_connecte
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
		
		if(!session()->has('utilisateur_eden_ecommerce')) {
			
			return redirect()->route('ecommerce.connexion');
        } 

        if(!defined('id_utilisateur'))
		    define('id_utilisateur', 7246);

        return $next($request);
    }
}
