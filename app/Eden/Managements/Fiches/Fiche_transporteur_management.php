<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;
//use App\Eden\Variables;

use DB;

/**
 * Gestion des fiches projets
 */
class Fiche_transporteur_management extends Fiche_management {
	
	/**
	 * 
	 * On retouche le champ utilisateurs
	 * 
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {
		
		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);

		$donnees['tarifs'] = modele('transporteur_tarif_livraison')
							->where('transporteur_id', $this->id_element)
							->join('zone_transporteur', 'zone_transporteur.id', 'transporteur_tarif_livraison.zone_id')
							->select('transporteur_tarif_livraison.*', 'zone_transporteur.nom')
							->get() ;
		
		return $donnees;
	}
	
}
