<?php

namespace App\Eden\Managements\Elements;

class Commande_management extends Document_management {

	/**
	 *
	 * Pour mettre à jour les stocks
	 *
	 */
	protected function methodes_post_modification_document($modele, $modele_avant, $modifications) {

        // verifie s'il y a eu un changement d'entrepôt pour mettre les lignes à jour
        $this->verifie_si_mise_a_jour_entrepot($modele, $modele_avant);

		parent::methodes_post_modification_document($modele, $modele_avant, $modifications);
	}
	
	/**
	 * 
	 * Si il y a eu un changement d'entrepôt sur la commande, les mouvements de stocks ne sont pas mis à jour si les articles ne sont pas modifiés (pour des raisons de perf)
	 * Du coup on est obligé de le déclencher ici
	 * 
	 */
	protected function verifie_si_mise_a_jour_entrepot($modele, $modele_avant) {

		if(empty($modele_avant->id) || $modele['entrepot_id'] == $modele_avant['entrepot_id'])
			return;
		
		$lignes = $this->lignes_articles();
		
		foreach($lignes as $ligne) {
			
			$management = management($this->_type_element.'_lignes', $ligne->id, $ligne);
            $management->management_parent = $this;
			$management->mise_a_jour_stocks();
		}
	}

}
