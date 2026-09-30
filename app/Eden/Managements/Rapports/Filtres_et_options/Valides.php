<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Applique un filtre sur une liste de champs
 *
 */
class Valides {
	
	/**
	 * 
	 * Applique le filtre par champ
	 * 
	 */	
    public static function applique($rapport) {
		
		// on récupère les paramètres
		if(isset($rapport->parametres)) {
			
			$parametres = $rapport->parametres;
		}
		else {
			
			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}

		if(isset(request()->valides)) {
			$parametres['valides'] = request()->valides;
		} else {
			$parametres['valides'] = '0';
		}

		
		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		$filtre_boolen = ( isset($parametres['valides']) && $parametres['valides'] == '1' ? true : false );

		// Affiche la vue
		$rapport->option('valides', ['valides' => $filtre_boolen]);

		return $filtre_boolen;
    }
}