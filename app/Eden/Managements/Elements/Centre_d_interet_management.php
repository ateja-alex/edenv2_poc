<?php

namespace App\Eden\Managements\Elements;

class Centre_d_interet_management extends Element_management {
	
	/**
	 * 
	 * Comment doit on présenter les résultats de la recherche
	 * 
	 */
	public function affichage_pour_recherche() {
		
		return "nom AS affichage_pour_recherche";
	}	
}