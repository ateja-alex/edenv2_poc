<?php

namespace App\Eden\Managements\Elements;

class Achat_sur_document_management extends Element_management {
	
	/**
	 *
	 * On recalcule la marge sur le projet
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		// on met à jour la marge sur le projet
		if(!empty($this->modele->projet_id)) {
			
			$projet_management = management('projet', $this->modele->projet_id);
			
			$projet_management->calcul_marge();
		}
		
	}
}