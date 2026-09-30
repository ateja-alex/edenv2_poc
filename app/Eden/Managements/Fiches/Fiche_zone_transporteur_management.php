<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
//use App\Eden\Variables;

use DB;

/**
 * Gestion des fiches projets
 */
class Fiche_zone_transporteur_management extends Fiche_management {
	
	/**
	 * 
	 * On retouche le champ utilisateurs
	 * 
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {
		
		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);

		$donnees['cp'] = modele('zone_transporteur_cp')
							->where('zone_id', $this->id_element)
							->join('pays', 'pays.id', 'pays_id')
							->select('zone_transporteur_cp.*', 'pays.nom')
							->get() ;
		
		return $donnees;
	}
	
}
