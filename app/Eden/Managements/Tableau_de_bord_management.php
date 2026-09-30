<?php

namespace App\Eden\Managements;

use App\Eden\Models\Rapport_libre;
use App\Eden\Models\Liste_libre;

class Tableau_de_bord_management {

    /**
     * 
     * On les infos pour le tableau de bord type liste de rapports
     * 
     */
    public static function recupere_informations_tableau_de_bord_type_liste_de_rapports($tableau_de_bord) {
		
		$categories = modele('tableau_de_bord_liste_categorie')->where('tableau_de_bord_id', $tableau_de_bord->id)->orderBy('ordre')->get();
		
		foreach($categories as $categorie) {
			
			$categorie_rapports = array();
			
			$rapports = modele('tableau_de_bord_liste_rapport')
							->where('tableau_de_bord_id', $tableau_de_bord->id)
							->where('tableau_de_bord_liste_categorie_id', $categorie->id)
							->get();
							
			foreach($rapports as $rapport) {
				
				$rapport = Rapport_libre::where('id_rapport', $rapport->id_rapport)->first();
				
				if($rapport === null)
					continue;
				
				// on va chercher l'id de la liste libre
				$liste_libre = Liste_libre::where('id_rapport', $rapport->id_rapport)->first();
				
				$rapport->id_liste = $liste_libre->id;
				
				$categorie_rapports[] = array(
					'rapport' => $rapport,
					'liste' => service('page_adv')->informations_pour_liste($liste_libre->type_element, $liste_libre->id_rapport),
				);
			}
			
			$categorie->rapports = $categorie_rapports;
							
		}
		
		return $categories;
    }
	
	
    
}
