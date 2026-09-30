<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * 
 *
 */
class Client {
	
	/**
	 * 
	 * Applique le filtre par Client
	 * 
	 */	
    public static function applique($rapport) {
		
    	$clients = modele('client')->orderBy('nom')->get()->pluck('nom', 'id')->toArray();

		// on récupère les paramètres
		if(isset($rapport->parametres)) {

			$parametres = $rapport->parametres;
		}
		else {

			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}

		if(!empty(request()->client)) {
            $parametres['client'] = request()->client;
        }

		if(!isset($parametres['client']) || $parametres['client'] == 'null'){
            $parametres['client'] = '';
        }

		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		// Affiche la vue
		$rapport->option('client', ['client' => $parametres['client'], 'clients' => $clients]);

		return $parametres['client'];
    }	
}