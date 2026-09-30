<?php

namespace App\Eden\Managements\Elements;

class Apporteur_affaire_management extends Element_management {
	
	/**
	 * 
	 * Comment doit on présenter les résultats de la recherche
	 * 
	 */
	public function affichage_pour_recherche() {
		
		return "CONCAT(societe,' ',prenom,' ',nom) AS affichage_pour_recherche";
	}

	
}