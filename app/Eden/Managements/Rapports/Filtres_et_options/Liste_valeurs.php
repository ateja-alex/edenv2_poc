<?php

namespace App\Eden\Managements\Rapports\Filtres_et_options;

use App\Eden\Managements\Rapports\Rapports_management;

/**
 *
 * Filtres pour choisir une ou plusieurs valeurs parmis une liste
 *
 */
class Liste_valeurs {
	
	/**
	 * 
	 * 
	 * 
	 */	
    public static function applique($rapport, $nom_liste = '') {
		
		// on récupère les paramètres
		if(isset($rapport->parametres)) {
			
			$parametres = $rapport->parametres;
		}
		else {
			
			$parametres = Rapports_management::recupere_parametres($rapport->id_rapport);
		}
		
		if(!empty(request()->{$nom_liste})) {
			
			if(request()->{$nom_liste} == 'false')
				$valeurs = array();
			else
				$valeurs = request()->{$nom_liste};
		}
		else {
			
			if(isset($parametres[$nom_liste]))
				$valeurs = $parametres[$nom_liste];
			else
				$valeurs = array();
		}
		
		// on va enregistrer les dates
		$parametres = array($nom_liste => $valeurs);
		
		Rapports_management::enregistre_parametres($rapport->id_rapport, $parametres, true);

		$rapport->option($nom_liste, [
            'valeurs' => [
                'id' => 'liste-' . $nom_liste,
                'valeurs' => $valeurs
            ],
            'filtre' => [
                'index_traduction' => 'rapport.filtres.' . $nom_liste,
                'type_filtre' => $nom_liste == 'utilisateur' || $nom_liste == 'famille' ? 'filtre-'.$nom_liste : 'filtre-liste-libre',
                'id' => 'liste-' . $nom_liste,
                'nom_sql' => $nom_liste
            ]
        ]);
		
		return $valeurs;
    }
	
	
	
}