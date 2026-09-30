<?php

namespace App\Eden\Managements\Elements;

class Lot_mouvement_management extends Element_management {

	/**
	 * 
	 * Affiche une colonne sur les listes qui est un lien vers le document
	 * 
	 */
	public function liste_lien_document($modele) {

		return management($modele->type_element, $modele->element_id)->affiche_lien();
	}

	
}
