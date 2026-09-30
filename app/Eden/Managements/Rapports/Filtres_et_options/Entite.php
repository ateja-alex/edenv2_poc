<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;
// use App\Eden\Managements\Rapports\Filtres_et_options\Liste_valeur_unique;

/**
 *
 * Filtres pour choisir une entité unique
 *
 */
class Entite {
	
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
		
		$valeur = modele('entite')->first()->id;
		
		if(!empty(request()->get('entite'))) {
			
			$valeur = request()->get('entite');
		}
		else {
			
			if(isset($parametres['entite'])) {
				
				$valeur = $parametres['entite'];
			}
		}
		
		// on va enregistrer l'entité choisie
		$parametres = array('entite' => $valeur);
		
		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		$rapport->option('entite', array('valeur' => $valeur));
		
		return $valeur;
    }
	
	
	
}