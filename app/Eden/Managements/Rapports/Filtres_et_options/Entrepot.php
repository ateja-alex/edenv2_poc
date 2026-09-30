<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;
// use App\Eden\Managements\Rapports\Filtres_et_options\Liste_valeur_unique;

/**
 *
 * Filtres pour choisir un entrepot unique
 *
 */
class Entrepot {
	
	/**
	 * 
	 * 
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
		
		$valeur = modele('entrepot')->first()->id;
		
		if(!empty(request()->get('entrepot'))) {
			
			$valeur = request()->get('entrepot');
		}
		else {
			
			if(isset($parametres['entrepot'])) {
				
				$valeur = $parametres['entrepot'];
			}
		}
		
		// on va enregistrer l'entité choisie
		$parametres = array('entrepot' => $valeur);
		
		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		$rapport->option('entrepot', array('valeur' => $valeur));
		
		return $valeur;
    }
	
	
	
}