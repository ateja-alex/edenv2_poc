<?php

namespace App\Eden\Managements\Fiches;

use App\Eden\Managements\Fiche_management;


/**
 * Gestion des fiches leads
 */
class Fiche_lead_management extends Fiche_management {
	
	/**
	 *
	 * Prépare les données pour la fiche
	 *
	 * @attention si vous utilisez cette méthode dans les classes filles, il faut faire appelle à parent::prepare_donnees_pour_fiche($donnees)
	 *
	 */
	public function prepare_donnees_pour_fiche($donnees = array()) {
		
		// on va chercher les données de base
		$donnees = parent::prepare_donnees_pour_fiche($donnees);

		// on va ajouter les intérêts
		$donnees['interets_leads_entite'] = $this->interets_entites();
		$donnees['interets_leads_article'] = $this->interets_articles();
		
		return $donnees;
	}
	
	/**
	 *
	 * Retourne les intérêts (entités)
	 *
	 * @return collection
	 *
	 */
	public function interets_entites() {
		
		$interets = modele('interet_lead_entite')->where('lead_id', $this->id_element)->get();

		return $interets;
	}
	
	/**
	 *
	 * Retourne les intérêts (articles)
	 *
	 * @return collection
	 *
	 */
	public function interets_articles() {
		
		$interets = modele('interet_lead_article')->where('lead_id', $this->id_element)->get();
		
		// dd($interets);
		
		return $interets;
	}
	
	

}
