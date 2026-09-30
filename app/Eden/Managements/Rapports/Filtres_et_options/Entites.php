<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Filtres_et_options\Liste_valeurs;

/**
 *
 * Filtres pour choisir une ou plusieurs entités
 *
 */
class Entites {
	
	/**
	 * 
	 * 
	 * 
	 */	
    public static function applique($rapport) {
		
		$liste_entites = Liste_valeurs::applique($rapport, 'entites');
		
		if(empty($liste_entites)) {
			
			// on retourne une liste par défaut
			$liste_entites = modele('entite')->where(function($requete) {
					$requete->where('exclure_des_rapports', 0);
					$requete->orWhereNull('exclure_des_rapports');
				})->orderBy('nom')->get()->pluck('id');
		}
		
		return $liste_entites;
    }
	
	
	
}