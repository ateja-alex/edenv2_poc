<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
use App\Eden\Managements\Listes_management;

/**
 *
 * Gestion des fiches de maintenance
 *
 */
class Fiche_maintenance_management extends Fiche_management {
	
	/**
	 * 
	 * Prépare les données pour la fiche
	 * 
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appel à parent::prepare_donnees_pour_fiche($donnees)
	 * 
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {
		

		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);
		
		return $donnees;
	}
	
}