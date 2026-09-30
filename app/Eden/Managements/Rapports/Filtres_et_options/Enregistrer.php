<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Permet d'enregistrer les données saisies sur un rapport
 *
 */
class Enregistrer {
	
	/**
	 * 
	 * Applique le bouton enregistrer
	 * 
	 */	
    public static function applique($rapport) {
		
		$rapport->option('enregistrer');
		
		return true;
    }
}