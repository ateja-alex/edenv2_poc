<?php

namespace App\Eden\Managements\Elements;

class Relance_recouvrement_management extends Element_management {

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'valider';

        return $liste_options;
    }
	
	/**
	 * 
	 * Affiche une colonne sur les listes qui est un lien vers le document
	 * 
	 */
	public function liste_lien_document($modele) {
		
		if(empty($modele->type_element))
			$modele->type_element = 'facture_vente';
		
		return management($modele->type_element, $modele->facture_vente_id)->affiche_lien();
	}
}