<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * 
 *
 */
class Fournisseur {
	
	/**
	 * 
	 * Applique le filtre par Fournisseur
	 * 
	 */	
    public static function applique($rapport) {
		
    	$fournisseurs = modele('fournisseur')->orderBy('nom')->get()->pluck('nom', 'id')->toArray();

		// on récupère les paramètres
		if(isset($rapport->parametres)) {

			$parametres = $rapport->parametres;
		}
		else {

			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}

		if(!empty(request()->fournisseur)) {
            $parametres['fournisseur'] = request()->fournisseur;
        }

		if(!isset($parametres['fournisseur']) || $parametres['fournisseur'] == 'null'){
            $parametres['fournisseur'] = '';
        }

		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		// Affiche la vue
		$rapport->option('fournisseur', ['fournisseur' => $parametres['fournisseur'], 'fournisseurs' => $fournisseurs]);

		return $parametres['fournisseur'];
    }	
}