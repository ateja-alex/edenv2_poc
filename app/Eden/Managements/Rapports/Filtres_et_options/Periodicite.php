<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Filtres pour choisir une périodicité sur un rapport (quotidienne, hebdo, mensuelle, trimestrielle, semestrielle, annuelle)
 *
 */
class Periodicite {
	
	/**
	 * 
	 * Applique le filtre dates mensuelles
	 * 
	 */	
    public static function applique($rapport, $dates = array()) {
		
		// on récupère les paramètres
		if(isset($rapport->parametres)) {
			
			$parametres = $rapport->parametres;
		}
		else {
			
			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}
		
		$periodicite = 'mensuelle';
		
		if(isset($parametres['periodicite']))
			$periodicite = $parametres['periodicite'];
		
		if(!empty(request()->periodicite)) {
			
			$periodicite = request()->periodicite;
		}
		
		
		
		// on va enregistrer les dates
		$parametres = array('periodicite' => $periodicite);
		
		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		$rapport->option('periodicite', array(
			
			'periodicite' => $periodicite,
		));

		return array('periodicite' => $periodicite, 'periodes' => self::periodes($periodicite, $dates));
    }
	
}