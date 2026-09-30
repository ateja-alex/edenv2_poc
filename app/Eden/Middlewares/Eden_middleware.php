<?php

namespace App\Eden\Middlewares;

use App\Eden\Models\Traduction_interface;
use App\Eden\Models\Log_page;

use Closure;

class Eden_middleware
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
		// on force le https
        if (!$request->secure() && env('APP_ENV') != "local") {
            return redirect()->secure($request->getRequestUri());
        }
		
		// on définie une constante pour dire qu'on est dans le cadre de l'ERP
		// !! attention, ce middleware ne doit donc jamais être appelé depuis la partie ecommerce du projet !!
		// !! attention, ce middleware est utilisé pour la gestion des profils dans les requetes !!
		define('eden_erp', true);

        if (session()->get('locale') != null) {

            $langue = session()->get('locale');
            \App::setlocale($langue);
        }
		
		// on logue chaque demande
		$demandes_non_loguees = array('/eden/notifications/recuperer');

		if(!in_array(request()->getPathInfo(), $demandes_non_loguees)) {

			$log_page = new Log_page;
			
			if(defined('id_utilisateur'))
				$log_page->utilisateur_id = id_utilisateur;
				
			$log_page->url = request()->getPathInfo();
			$log_page->methode = request()->getMethod();
			$log_page->date = date('Y-m-d H:i:s');
			
			$log_page->save();
		}

		// l'affichage du menu par défaut
		if(parametre_utilisateur('type_menu') === null)
			parametre_utilisateur('type_menu', 1);
		
		return $next($request);
    }
}
