<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Gestion des exports excel
 *
 */
class Familles_de_produits {

    public static function applique($rapport) { 
		
        // on récupère les paramètres
        if(isset($rapport->parametres)) {
			
			$parametres = $rapport->parametres;
		}
		else {
			
			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}
		
		$valeurs = array();

        if(!empty(request()->get('familles_de_produits'))) {

            $valeurs = request()->get('familles_de_produits');
        }
        else {

            if(isset($parametres['familles_de_produits'])) {
				$valeurs = $parametres['familles_de_produits'];
			}
        }
		
		if(!is_array($valeurs))
			$valeurs = array();

        if($valeurs =='false'){
            $valeurs = [];
        }


        $parametres = array('familles_de_produits' => $valeurs);

        Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		$famille_management = management('famille');
		
		$familles = modele('famille')->zero_ou_null('parent_id')->orderBy('nom')->get();
		
		$familles_pour_la_vue = array();
		
		foreach($familles as $famille) {
			
			$familles_pour_la_vue[] = $famille_management->contenu_famille($famille);
		}

        $rapport->option('familles_de_produits', array('familles_de_produits' => $valeurs, 'arborescence_famille' => $familles_pour_la_vue));

        return $valeurs;
    }
	
	
}