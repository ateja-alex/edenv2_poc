<?php

namespace App\Eden\Champs;

class Champ_montant_decimal extends Champ_montant {
	
	public $decimales = 2;

	/**
	 * 
	 * On retouche éventuellement une valeur pour l'import de données en masse
	 * 
	 */
	public function prepare_pour_import($valeur) {
		
		// on remplace les virgules par des points
		$valeur = str_replace(',', '.', $valeur);
		
		// autres remplacements
		$valeur = str_replace(array(' ', '€', maquette('devise_application_symbole')), '', $valeur);
		
		return $valeur;
	}
}