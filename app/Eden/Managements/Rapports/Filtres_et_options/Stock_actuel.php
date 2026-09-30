<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Applique un tri décroissant par stock_actuel
 *
 */
class Stock_actuel {

    public static function applique($rapport) { 
		
        // on récupère les paramètres
        if(isset($rapport->parametres)) {
			
			$parametres = $rapport->parametres;
		}
		else {
			
			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}
		
		
		$valeur = null;
		
		if(request()->has('stock_actuel')) {
			
            $valeur = request()->get('stock_actuel');
        }
        else {

            if(isset($parametres['stock_actuel'])) {
				$valeur = $parametres['stock_actuel'];
			}
        }
		
		$parametres = array('stock_actuel' => $valeur);
		
		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		$rapport->option('stock_actuel', array('tri' => $valeur));
		return $valeur;
    }
	
	
}