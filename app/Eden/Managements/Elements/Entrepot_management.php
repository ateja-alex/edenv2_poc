<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;

class Entrepot_management extends Element_management {
	
	/**
	 * 
	 * @cf description sur Element_management
	 * 
	 * on vide le cache à l'enregistrement d'un nouvel élément
	 * 
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

		// on vide le cache
		Cache_management::vider();

		parent::methodes_post_modification($modele, $modele_avant, $modifications);
	}
	
	/**
	 * 
	 * @cf description sur Element_management
	 * 
	 * on vide le cache à la suppression d'un nouvel élément
	 * 
	 */
	protected function methodes_post_suppression($modele) {
		
		// on vide le cache
		Cache_management::vider();
		
		parent::methodes_post_suppression($modele);
	}

}