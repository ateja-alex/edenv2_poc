<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Variables;

/**
 *
 * Pour ajouter un texte d'aide sur le rapport
 *
 */
class Aide {
	
	/**
	 * 
	 * Passe un texte d'aide qui décrit le rapport
	 * 
	 */	
    public static function applique($rapport, $texte) {
		
		$rapport->option('aide', array('texte_aide' => $texte));
	}
}