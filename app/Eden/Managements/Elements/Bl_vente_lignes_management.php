<?php

namespace App\Eden\Managements\Elements;

class Bl_vente_lignes_management extends Document_lignes_management {
	
	/**
	 *
	 * Trigger post suppression
	 *
	 * @return void
	 *
	 */
	protected function methodes_post_suppression($modele) {

		parent::methodes_post_suppression($modele);
		
		// on met à jour les stocks réservés
		$this->mise_a_jour_stocks(false);
	}
	
	/**
	 * 
	 * On crée les lignes à réceptionner pour les fournisseurs
	 * 
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		// on met à jour les stocks réservés
		$this->mise_a_jour_stocks();
	}
}