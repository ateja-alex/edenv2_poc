<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Applique un filtre sur une liste de champs
 *
 */
class Champ {
	
	/**
	 * 
	 * Applique le filtre par champ
	 * 
	 */	
    public static function applique($rapport, $champs) {
		
		// on récupère les paramètres
		if(isset($rapport->parametres)) {
			
			$parametres = $rapport->parametres;
		}
		else {
			
			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}

		$filtres = [];
		
		$champs_du_formulaire = request()->all();
		
		foreach($champs as $champ) {

			$nom_sql = $champ->modele->nom_sql;

			if(array_key_exists($nom_sql, $champs_du_formulaire) && !empty(request()->all())) {
				
				$filtres[$nom_sql] = request()->$nom_sql;
			}
			elseif(isset($parametres[$nom_sql])) {
				
				$filtres[$nom_sql] = $parametres[$nom_sql];
			}
		}

		// on va enregistrer les filtres
		if(!empty($filtres)) {
			
			$parametres = $filtres;
		}
		
		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);
		
		// Affiche la vue
		$rapport->option('champ', ['filtres' => $filtres, 'champs' => $champs]);

		return $filtres;
    }
}